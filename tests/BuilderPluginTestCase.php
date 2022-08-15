<?php namespace Winter\Builder\Tests;

if (class_exists('System\Tests\Bootstrap\PluginTestCase')) {
    class BaseTestCase extends \System\Tests\Bootstrap\PluginTestCase
    {
    }
} else {
    class BaseTestCase extends \PluginTestCase
    {
    }
}

abstract class BuilderPluginTestCase extends BaseTestCase
{
    protected $refreshPlugins = [
        'Winter.Builder',
    ];
}
