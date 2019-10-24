<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaManagerLockListTable extends Migration
{
    protected $tableName;

    public function __construct()
    {
        $this->tableName = config('mediaManager.table_locked', 'locked');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
