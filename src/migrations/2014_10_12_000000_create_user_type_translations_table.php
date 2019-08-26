<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTypeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
Schema::create('user_type_translations', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->bigInteger('user_type_id', false, true)->index();
    $table->string('name', 40);
    $table->text('description')->nullable();
    $table->string('locale', 10)->index();
    $table->timestamps();
    $table->unique(['locale', 'user_type_id']);
    $table->foreign('user_type_id')
        ->references('id')->on('user_types')
            ->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_type_translations');
    }
}
