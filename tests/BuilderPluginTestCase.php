<?php

namespace Winter\Builder\Tests;

use System\Tests\Bootstrap\PluginTestCase;

abstract class BuilderPluginTestCase extends PluginTestCase
{
    protected $refreshPlugins = [
        'Winter.Builder',
    ];
}
