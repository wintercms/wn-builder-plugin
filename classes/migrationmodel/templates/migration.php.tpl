<?php

namespace {namespace};

use Winter\Storm\Support\Facades\Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class {className} extends Migration
{
    public function up()
    {
        Schema::create('{tableNamePrefix}_table', function (Blueprint $table) {
            // Your migration code here.
        });
    }

    public function down()
    {
        Schema::drop('{tableNamePrefix}_table');
    }
}
