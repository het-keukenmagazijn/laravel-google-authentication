<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleOauthTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_oauth_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id', false, true)->index();
            $table->string('access_token');
            $table->string('refresh_token');
            $table->text('id_token');
            $table->timestamp('expires_at');
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
        Schema::dropIfExists('user_google_oauth_tokens');
    }
}
