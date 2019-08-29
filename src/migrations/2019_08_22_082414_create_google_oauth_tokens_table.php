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
        if (config('google_identity.migrations.create_views_instead_of_tables')) {
            $__connectionAlias = config('google_identity.migrations.view_source_connection_alias');
            $__connectionDatabase = config('google_identity.migrations.view_source_database_name');
            $_viewable = Schema::connection($__connectionAlias)
                ->hasTable('google_oauth_tokens');

            if (!$_viewable) {
                throw new \Exception("Table google_oauth_tokens does not exist on source %s", $__connectionDatabase);
            }

            // Prepare & create the view.
            $sQuery = sprintf(
                "CREATE OR REPLACE VIEW %s AS SELECT * FROM %s.%s WITH CASCADED CHECK OPTION",
                'google_oauth_tokens', $__connectionDatabase, 'google_oauth_tokens'
            );
            \DB::statement($sQuery);
        } else {
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
