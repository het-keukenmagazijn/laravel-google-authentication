## Introduction
This package allows users to quickly authenticate using their Google account. You are able to specify a domain the user' account must be in - or you can just allow anyone to register.

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
            $table->bigInteger('user_type_id', false, true)->index();
            $table->string('google_account_id')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('avatar')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_type_id', 'email']);
            $table->foreign('user_type_id')
                ->references('id')->on('user_types')
                    ->onDelete('restrict');
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

If you have already ran the default `php artisan make:auth` command you will need to change your columns to uphold the same columns and their types mentioned above.

The package provides the rest of the required migrations, such as user_roles, roles, user_types and a google_oauth_tokens_table.

Once you have configured the migration you will have to publish the configuration file by running `php artisan vendor:publish --tag=km`. Be sure to edit the package properly to your environment.

Once your package is published you will have to add the `Keukenmagazijn\LaravelGoogleAuthentication` Trait to your User model, ie.:
```php
class User extends Model {
    use Keukenmagazijn\LaravelGoogleAuthentication\UsesGoogleIdentity;
}
```

You can render the authorization button by adding the following code to your blade:
```php
{!! (new Keukenmagazijn\LaravelGoogleAuthentication\GoogleIdentityFacade())->renderAuthorizeButton() !!}
```

When a user authorizes it will check if the user account is within the whitelisted domains *(configured in your `config/google_identity.php` file)*, or if you allowed any domain to register, it will always succeed when permissions are given.

Users authorizing using Google will automatically gain the 'employee' role.

The following models/entities should be available in your application.

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

##### UserType.php
```php
<?php

namespace App\Entities;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        '_key'
    ];

    public $translatedAttributes = [
        'name', 'description'
    ];
}
```

##### UserTypeTranslation.php
```php
<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class UserTypeTranslation extends Model
{
    protected $fillable = [
        'name', 'description'
    ];

    public function userType() {
        $this->hasOne(UserType::class);
    }
}
```
