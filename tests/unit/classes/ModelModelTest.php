<?php

namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\ModelModel;
use Winter\Builder\Classes\PluginCode;
use Winter\Builder\Tests\BuilderPluginTestCase;
use Winter\Storm\Exception\SystemException;

#[\PHPUnit\Framework\Attributes\CoversClass(ModelModel::class)]
class ModelModelTest extends BuilderPluginTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        // Ensure cleanup for testGetModelFields
        @unlink(__DIR__.'/../../../models/MyMock.php');
    }

    public function test_it_validates_model_class_names()
    {
        $unQualifiedClassName = 'MyClassName';
        $this->assertTrue(ModelModel::validateModelClassName($unQualifiedClassName));

        $qualifiedClassName = 'Winter\Builder\Models\Settings';
        $this->assertTrue(ModelModel::validateModelClassName($qualifiedClassName));

        $fullyQualifiedClassName = '\Winter\Builder\Models\Settings';
        $this->assertTrue(ModelModel::validateModelClassName($fullyQualifiedClassName));

        $qualifiedClassNameStartingWithLowerCase = 'winter\Builder\Models\Settings';
        $this->assertTrue(ModelModel::validateModelClassName($qualifiedClassNameStartingWithLowerCase));
    }

    public function test_it_does_not_validate_invalid_model_class_names()
    {
        $unQualifiedClassName = 'myClassName'; // starts with lower case
        $this->assertFalse(ModelModel::validateModelClassName($unQualifiedClassName));

        $qualifiedClassName = 'MyNameSpace\MyPlugin\Models\MyClassName'; // namespace\class doesn't exist
        $this->assertFalse(ModelModel::validateModelClassName($qualifiedClassName));

        $fullyQualifiedClassName = '\MyNameSpace\MyPlugin\Models\MyClassName'; // namespace\class doesn't exist
        $this->assertFalse(ModelModel::validateModelClassName($fullyQualifiedClassName));
    }

    public function test_it_can_extract_model_fields_from_model()
    {
        // Invalid Class Name
        try {
            ModelModel::getModelFields(null, 'myClassName');
        } catch (SystemException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid model class name: myClassName');
            return;
        }

        // Directory Not Found
        $pluginCodeObj = PluginCode::createFromNamespace('MyNameSpace\MyPlugin\Models\MyClassName');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyClassName'));

        // Directory Found, but Class Not Found
        $pluginCodeObj = PluginCode::createFromNamespace('Winter\Builder\Models\MyClassName');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyClassName'));

        // Model without Table Name
        $pluginCodeObj = PluginCode::createFromNamespace('Winter\Builder\Models\Settings');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'Settings'));

        // Model with Table Name
        copy(__DIR__."/../../fixtures/MyMock.php", __DIR__."/../../../models/MyMock.php");
        $pluginCodeObj = PluginCode::createFromNamespace('Winter\Builder\Models\MyMock');
        $this->assertEquals([], ModelModel::getModelFields($pluginCodeObj, 'MyMock'));
    }
}
