<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleUsersTable extends Migration
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
                ->hasTable('role_user');

            if (!$_viewable) {
                throw new \Exception("Table role_user does not exist on source %s", $__connectionDatabase);
            }

            // Prepare & create the view.
            $sQuery = sprintf(
                "CREATE OR REPLACE VIEW %s AS SELECT * FROM %s.%s WITH CASCADED CHECK OPTION",
                'role_user', $__connectionDatabase, 'role_user'
            );
            \DB::statement($sQuery);
        } else {
            if (!Schema::hasTable('role_user')) {
                Schema::create('role_user', function (Blueprint $table) {
                    $table->bigInteger('user_id', false, true)->index();
                    $table->bigInteger('role_id', false, true)->index();
                    $table->unique(['user_id', 'role_id']);
                    $table->foreign('user_id')
                        ->references('id')->on('users')
                        ->onDelete('cascade')->onUpdate('cascade');
                    $table->foreign('role_id')
                        ->references('id')->on('roles')
                        ->onDelete('cascade')->onUpdate('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
