<?php namespace Keukenmagazijn\LaravelGoogleAuthentication\Controllers;

use Keukenmagazijn\LaravelGoogleAuthentication\Facades\GoogleIdentityFacade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleIdentityController
{
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function callback(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'string|required',
            'scope' => 'string|required'
        ]);

        /** @var GoogleIdentityFacade $_facade */
        $_facade = \App::make(GoogleIdentityFacade::class);
        try {
            $_user = $_facade->syncUserDataIntoApplication($request->code);
        } catch (\Exception $e) {
            throw $e;
        }
        \Auth::login($_user);
        return \Redirect::route(config('google_identity.callback_redirect_route_name'));
    }
}
