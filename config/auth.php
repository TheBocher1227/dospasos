<?php

return [

    /*
    |----------------------------------------------------------------------
    | Authentication Defaults
    |----------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |----------------------------------------------------------------------
    | Authentication Guards
    |----------------------------------------------------------------------
    |
    | Here you may define every authentication guard for your application.
    | The default configuration uses session storage and the Eloquent user 
    | provider, but you may define additional guards if needed.
    |
    | Supported: "session", "api", "jwt", etc.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt',  // JWT authentication driver
            'provider' => 'users', // Define the provider for api
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | User Providers
    |----------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved from your database or other storage
    | mechanisms used by the application to persist your user's data.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,  // This should point to your User model
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Password Reset Configuration
    |----------------------------------------------------------------------
    |
    | If you have more than one user table or model in the application and
    | you want to have separate password reset configurations, you may do so
    | here. The expire time is the number of minutes that each reset token 
    | will be valid. You can adjust this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Password Confirmation Timeout
    |----------------------------------------------------------------------
    |
    | You may define the amount of seconds before a password confirmation
    | times out and prompts the user to re-enter their password. By default,
    | this timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,
];
