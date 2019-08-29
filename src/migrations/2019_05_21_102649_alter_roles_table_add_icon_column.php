<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRolesTableAddIconColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('google_identity.migrations.create_views_instead_of_tables')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'icon')) {
                    $table->string('icon', 20);
                }
                if (!Schema::hasColumn('roles', 'powerlevel')) {
                    $table->integer('powerlevel', false, true)->unique()->index()->autoIncrement();
                }
                if (!Schema::hasColumn('roles', 'active')) {
                    $table->boolean('active')->default(1)->index();
                }
                if(!Schema::hasColumn('roles', '_key')) {
                    $table->string('_key', 20)->unique();
                }
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
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->dropColumn('powerlevel');
        });
    }
}
