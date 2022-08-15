<?php namespace Winter\Builder\Tests;

if (class_exists('System\Tests\Bootstrap\TestCase')) {
    class BaseTestCase extends \System\Tests\Bootstrap\TestCase
    {
    }
} else {
    class BaseTestCase extends \TestCase
    {
    }
}

abstract class BuilderPluginTestCase extends BaseTestCase
{
    protected $refreshPlugins = [
        'Winter.Builder',
    ];
}
