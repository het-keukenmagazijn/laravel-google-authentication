## Introduction
This package allows users to quickly authenticate using their Google account with Google Identity. You are able to specify a domain the user' account must be in - or you can just allow anyone to register.

The package extends and works together with the default Laravel authentication, but it requires modification and additions to the default tables provided by Laravel.

## Installation
Your users table will be reformed and you will have to have the following migration available in your application:
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('google_account_id')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
```

## Migrations
If you have already ran the default `php artisan make:auth` command you will need to change your columns to uphold the same columns and their types mentioned above.

The package provides the rest of the required migrations, such as user_roles, roles and a google_oauth_tokens_table.

You have the option to create either tables or to create views if your database architecture requires you to do so.
In the latter case you need to configure the configured alias name and the database name itself that you want to source the data from in the 
`config/google_identity.php` file and you will need to change the `migrations.create_views_instead_of_tables` value from false to `true`.

Or add the following config keys to your `.env` file and configure it with your config values:
```
GOOGLE_IDENTITY_USE_VIEWS=
GOOGLE_IDENTITIY_VIEW_CONNECTION_ALIAS=
GOOGLE_IDENTITIY_VIEW_DATABASE_NAME=
```

## Configuration
Once you have configured the migration you will have to publish the configuration file by running `php artisan vendor:publish --tag=km`. Be sure to edit the package properly to your environment.


## UsesGoogleIdentity trait
Once your package is published you will have to add the `Keukenmagazijn\LaravelGoogleAuthentication` Trait to your User model, ie.:
```php
class User extends Model {
    use Keukenmagazijn\LaravelGoogleAuthentication\UsesGoogleIdentity;
}
```

## Button rendering and Identity
You can render the authorization button by adding the following code to your blade:
```php
{!! (new Keukenmagazijn\LaravelGoogleAuthentication\GoogleIdentityFacade())->renderAuthorizeButton() !!}
```

When a user authorizes it will check if the user account is within the whitelisted domains *(configured in your `config/google_identity.php` file)*, or if you allowed any domain to register, it will always succeed when permissions are given.

Users authorizing using Google will automatically gain the 'employee' role.

## Required entities
The following models/entities should be available in your application.

##### User.php (this is my example)
```php
<?php

namespace App\Entities;

use Hash;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Keukenmagazijn\LaravelGoogleAuthentication\Traits\UsesGoogleIdentity;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use CanResetPassword;
    use UsesGoogleIdentity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'google_account_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param string $key
     * @return bool
     */
    public function hasRole(string $key): bool {
        return $this->roles()->where('_key', $key)->exists();
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return Role
     */
    public function getHighestRole():? Role {
        return $this->roles()->orderBy('powerlevel', 'desc')->first();
    }
}
```

##### GoogleOauthToken.php
```php
<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleOauthToken extends Model
{
    protected $fillable = [
        'user_id', 'access_token', 'refresh_token', 'id_token', 'expires_at'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
```

##### Role.php
```php

<?php

namespace App\Entities;

class Role
{
    /** @var array */
    protected $fillable = [
        'name', '_key', 'icon', 'powerlevel', 'active'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users () {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return string
     */
    public function getIcon(): string {
        if (empty($this->icon)) return 'fa fa-question-circle-o';
        return $this->icon;
    }
}
```

#### Google Identity
You need to set-up a route for your Google Identity to call back to, this is done in Google Identity itself.
Once you have those configuration settings, you should now fill add the following data to your `.env` file and fill it with the corresponding data:
```
GOOGLE_IDENTITY_APP_NAME=
GOOGLE_IDENTITY_CLIENT_ID=
GOOGLE_IDENTITY_CLIENT_SECRET=
GOOGLE_IDENTITY_REDIRECT_URI=
```

#### Routing
The next step is to set-up a get route for the `GOOGLE_IDENTITY_REDIRECT_URI` uri.
There is a controller available within the package to handle the callback for you and it will redirect you back to the named route defined as `callback_redirect_route_name` in the `config/google_identity.php` file.

If you want to customize the callback method you can set-up a custom controller for it and use the `GoogleIdentityController` as an example on how to use the `GoogleIdentityFacade`.

An example route where `GOOGLE_IDENTITY_REDIRECT_URI='http://appurl.extension/identity/callback'`:
```php
Route::get('identity/callback', [\Keukenmagazijn\LaravelGoogleAuthentication\Controllers\GoogleIdentityController::class, 'callback']);
```
