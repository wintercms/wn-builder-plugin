<?php namespace Winter\Builder\Tests\Fixtures\PluginFixture\Models;

use Model;

class SimpleModel extends Model
{
    /**
     * Database table.
     *
     * @var string
     */
    public $table = 'plugin_fixture_simple_model';

    /**
     * Used to make sure that the "setJsonable" method doesn't mess with the source code too much
     *
     * @return void
     */
    public function methodName()
    {
        return 'This is a test method';
    }
}
