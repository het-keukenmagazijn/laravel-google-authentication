<?php namespace Keukenmagazijn\LaravelGoogleAuthentication\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UsesGoogleIdentity
{
    /**
     * @param array $googleResponseData
     * [
     *  'email' => String,
     *  'givenName' => String|null,
     *  'hd' => String,
     *  'id' => Integer|String,
     *  'name' => null,
     *  'picture' => String,
     *  'verifiedEmail' => Boolean
     * ]
     * @return UsesGoogleIdentity
     */
    public function firstOrCreateGoogleUser(array $googleResponseData): self
    {
        try {
            $_user = $this->where('email', $googleResponseData['email'])
                ->firstOrFail();

            $_updateArray = [];
            if (empty($_user->name) || (!empty($googleResponseData['name']) && $_user->name != $googleResponseData['name'])) $_updateArray['name'] = $googleResponseData['name'];
            if (empty($_user->google_account_id)) $_updateArray['google_account_id'] = $googleResponseData['id'];
            if ($_user->avatar != $googleResponseData['picture']) $_updateArray['avatar'] = $googleResponseData['picture'];
            if (!empty($_updateArray)) {
                $_updateArray['updated_at'] = date('Y-m-d H:i:s');
                $_user->update($_updateArray);
            }
            return $_user;
        } catch (\Exception $e) {
            $_user = $this->create([
                'email' => $googleResponseData['email'],
                'avatar' => $googleResponseData['picture'],
                'name' => $googleResponseData['name'],
                'google_account_id' => $googleResponseData['id']
            ]);
            $_user->{config('google_identity.users.providers.roles.relationalName')}()->attach($this->_getOrCreateEmployeeRole());
            return $_user;
        }
    }

    /**
     * The data returned by Google when authenticating with your auth code.
     * @param array $tokenData
     * example: <[
     *  'access_token' => String,
     *  'expires_in' => Integer,
     *  'scope' => String,
     *  'token_type' => String,
     *  'id_token' => String,
     *  'created' => Integer
     * ]>
     */
    public function attachTokenDataToUser(array $tokenData): void
    {
        $_data = [
            'access_token' => $tokenData['access_token'],
            'id_token' => $tokenData['id_token'],
            'expires_at' => date("Y-m-d H:i:s", time() + $tokenData['expires_in']),
        ];
        if (isset($tokenData['refresh_token'])) $_data['refresh_token'] = $tokenData['refresh_token'];
        if (0 < $this->googleOauthToken()->count()) {
            $_data['updated_at'] = date('Y-m-d H:i:s');
            $this->googleOauthToken()->update($_data);
        } else {
            $this->googleOauthToken()->create($_data);
        }
    }

    /**
     * @return bool
     */
    public function isAccessTokenExpired(): bool
    {

    }

    /**
     * @return HasOne
     */
    public function googleOauthToken(): HasOne
    {
        return $this->hasOne(config('google_identity.users.providers.googleOauthToken.model'));
    }

    /**
     * @return Model
     */
    private function _getOrCreateEmployeeRole(): Model
    {
        $_model = \App::make(config('google_identity.users.providers.roles.model'));
        try {
            return $_model->where('_key', 'employee')->firstOrFail();
        } catch (\Exception $e) {
            return $_model->create([
                '_key' => 'employee',
                'name' => 'An employee',
                'active' => 1,
                'icon' => 'fa fa-user',
                'powerlevel' => 1
            ]);
        }
    }
}
