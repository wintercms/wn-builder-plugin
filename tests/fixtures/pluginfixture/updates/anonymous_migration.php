<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('plugin_fixture_simple_model')) {
            return;
        }

        Schema::create('plugin_fixture_simple_model', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        if (!Schema::hasTable('plugin_fixture_simple_model')) {
            return;
        }

        Schema::drop('plugin_fixture_simple_model');
    }
};
