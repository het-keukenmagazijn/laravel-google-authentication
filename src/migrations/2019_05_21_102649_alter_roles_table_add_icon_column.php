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
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'icon')) {
                $table->string('icon', 20);
            }
            if (!Schema::hasColumn('roles', 'powerlevel')) {
                $table->integer('powerlevel', false, true)->unique()->index()->autoIncrement();
            }
        });
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
