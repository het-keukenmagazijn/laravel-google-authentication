<?php return [
    'app_name' => env('GOOGLE_IDENTITY_APP_NAME', ''),
    'client_id' => env('GOOGLE_IDENTITY_CLIENT_ID', ''),
    'client_secret' => env('GOOGLE_IDENTITY_CLIENT_SECRET', ''),
    'redirect_uri' => env('GOOGLE_IDENTITY_REDIRECT_URI', ''),
    'scope' => env('GOOGLE_IDENTITY_SCOPE', ''),
    'callback_redirect_route_name' => 'landing',
    'template' => [
        'html' => [
            'authorizeButton' => '<a href="%s"><button class="button is-info is-fullwidth is-large"><span class="icon"><i class="fab fa-google is-large"></i></span><span> Sign in with Google</span></button></a>'
        ]
    ],
    'users' => [
        'providers' => [
            'users' => [
                'model' => config('auth.providers.users.model', 'App\\User')
            ],
            'roles' => [
                'model' => 'App\\Entities\\Role',
                'relationalName' => 'roles',
                'default_role_name' => 'employee'
            ],
            'googleOauthToken' => [
                'model' => 'App\\Entities\\GoogleOauthToken'
            ]
        ]
    ],
    'domains' => [
        'whitelist' => [
            'keukenmagazijn.nl'
        ],
        'any' => false,
    ]
];
