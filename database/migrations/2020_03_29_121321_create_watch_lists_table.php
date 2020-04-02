<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatchListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('watch_lists', static function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->autoIncrement()->unique();
            $table->uuid('uuid')->comment('store uuid of users in ghatreh');
            $table->bigInteger('nid')->unsigned()->comment('store nid of products');
            $table->timestamps();
            $table->index('id');
            $table->index('uuid');
            $table->index('nid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_lists');
    }
}
