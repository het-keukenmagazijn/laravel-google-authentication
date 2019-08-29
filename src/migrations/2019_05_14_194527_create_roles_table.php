<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
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
                ->hasTable('roles');
            
            if (!$_viewable) {
                throw new \Exception("Table roles does not exist on source %s", $__connectionDatabase);
            }

            // Prepare & create the view.
            $sQuery = sprintf(
                "CREATE OR REPLACE VIEW %s AS SELECT * FROM %s.%s WITH CASCADED CHECK OPTION",
                'roles', $__connectionDatabase, 'roles'
            );
            \DB::statement($sQuery);
        } else {
            if (!Schema::hasTable('roles')) {
                Schema::create('roles', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('name', 40)->unique();
                    $table->string('_key', 20)->unique();
                    $table->boolean('active')->default(1)->index();
                    $table->timestamps();
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
        Schema::dropIfExists('roles');
    }
}
