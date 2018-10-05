<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaManagerLockListTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('locked', function (Blueprint $table) {
            $table->integer('id');
            $table->string('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('locked');
    }
}
