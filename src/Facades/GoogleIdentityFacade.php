<?php namespace Keukenmagazijn\LaravelGoogleAuthentication\Facades;

use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Database\Eloquent\Model;

class GoogleIdentityFacade
{
    /** @var Google_Client */
    private static $_googleClient;
    /** @var Google_Service_Oauth2 */
    private static $_oauth2Client;

    /**
     * @return null|string
     */
    public function renderAuthorizeButton():? string
    {
        if (!$this->needsToAuthorize()) return null;
        /** @noinspection HtmlUnknownTarget */
        return sprintf(config('google_identity.template.html.authorizeButton'), filter_var($this->getGoogleClient()->createAuthUrl(), FILTER_SANITIZE_URL));
    }

    /**
     * @return bool
     */
    public function needsToAuthorize(): bool
    {
        return empty($this->getGoogleClient()->getAccessToken());
    }

    /**
     * @param string $code
     * @return Model
     * @throws \Exception
     */
    public function syncUserDataIntoApplication(string $code): Model
    {
        if (empty($this->getGoogleClient()->getAccessToken())) {
            $_tokenData = $this->_getGoogleOauth2Tokens($code);
        }
        $_response = (array) $this->getOauth2Client()->userinfo_v2_me->get();

        if ($this->_mayUserBeRegistered($_response['hd'])) {
            $_user = $this->_getUserEntity()->firstOrCreateGoogleUser($_response);
            return $_user;
        } else {
            throw new \Exception("This Google account is not allowed to use this application per domain rules.");
        }
    }

    /**
     * This method applies domain whitelisting, if enabled.
     * @param string|null $domain
     * @return bool
     */
    private function _mayUserBeRegistered(string $domain = null): bool
    {
        if (config('google_identity.domains.any')) return true;
        if (empty($domain)) return false;
        return isset(array_flip(config('google_identity.domains.whitelist'))[$domain]);
    }

    /**
     * @return Model
     */
    private function _getUserEntity (): Model
    {
        return \App::make(config('google_identity.users.providers.users.model'));
    }

    /**
     * @param string $code
     * @return array
     */
    private function _getGoogleOauth2Tokens(string $code)
    {
        return $this->getGoogleClient()->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * @return Google_Service_Oauth2
     */
    public function getOauth2Client(): Google_Service_Oauth2
    {
        if (empty(self::$_oauth2Client)) {
            self::$_oauth2Client = new \Google_Service_Oauth2($this->getGoogleClient());
        }
        return self::$_oauth2Client;
    }

    /**
     * @return Google_Client;
     */
    public function getGoogleClient(): Google_Client
    {
        if (empty(self::$_googleClient)) {
            self::$_googleClient = $this->_configureGoogleClient(new Google_Client);
        }
        return self::$_googleClient;
    }

    /**
     * @param Google_Client $client
     * @return Google_Client
     */
    private function _configureGoogleClient(Google_Client $client): Google_Client
    {
        $client->setAccessType('offline');
        $client->setApplicationName(config('google_identity.app_name'));
        $client->setClientId(config('google_identity.client_id'));
        $client->setClientSecret(config('google_identity.client_secret'));
        $client->setRedirectUri(config('google_identity.redirect_uri'));
        $client->addScope('https://www.googleapis.com/auth/cloud-identity.groups');
        $client->addScope('https://www.googleapis.com/auth/userinfo.email');

        if (!empty(\Auth::user())) {
            $_tokens = \Auth::user()->googleOauthToken;
            if (!empty($_tokens)) {
                $client->setAccessToken($_tokens->access_token);
                if (date('Y-m-d H:i:s') >= $_tokens->expires_at) {
                    $_newTokens = $client->fetchAccessTokenWithRefreshToken($_tokens->refresh_token);
                    $_tokens->update([
                        'refresh_token' => $_newTokens['refresh_token'],
                        'access_token' => $_newTokens['access_token'],
                        'updated_at' => date('Y-m-d H:i:s'),
                        'expires_at' => date('Y-m-d H:i:s', time()+$_newTokens['expires_in'])
                    ]);
                    $client->setAccessToken($_newTokens['access_token']);
                }
            }
        }
        return $client;
    }
}
