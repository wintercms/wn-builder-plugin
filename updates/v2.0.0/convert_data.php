<?php namespace Winter\Builder\Updates;

use Db;
use Winter\Storm\Database\Updates\Migration;

class ConvertData extends Migration
{
    public function up()
    {
        Db::table('system_settings')->where('item', 'rainlab_builder_settings')->update(['item' => 'winter_builder_settings']);
    }

    public function down()
    {
        Db::table('system_settings')->where('item', 'winter_builder_settings')->update(['item' => 'rainlab_builder_settings']);
    }
}
