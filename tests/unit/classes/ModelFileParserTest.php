<?php

namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\ModelFileParser;
use Winter\Builder\Tests\BuilderPluginTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(ModelFileParser::class)]
class ModelFileParserTest extends BuilderPluginTestCase
{
    public function test_it_can_get_information_for_model_from_parser()
    {
        $parser = ModelFileParser::fromFile(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $modelInfo = $parser->extractModelInfoFromSource();

        $this->assertEquals('Winter\Builder\Tests\Fixtures\PluginFixture\Models', $modelInfo['namespace']);
        $this->assertEquals('SimpleModel', $modelInfo['class']);
        $this->assertEquals('plugin_fixture_simple_model', $modelInfo['table']);
    }

    public function test_it_can_generate_and_provide_source_of_given_model_from_parser()
    {
        $parser = ModelFileParser::fromFile(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $source = $parser->getSource();

        $this->assertEquals(file_get_contents(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php'), $source);
    }

    public function test_it_can_get_value_of_jsonable_from_model()
    {
        $parser = ModelFileParser::fromFile(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $jsonable = $parser->getJsonable();
        $this->assertNull($jsonable);

        $parser = ModelFileParser::fromFile(__DIR__ . '/../../fixtures/pluginfixture/models/ArrayDataModel.php');
        $jsonable = $parser->getJsonable();
        $this->assertEquals([
            'data',
        ], $jsonable);
    }

    public function test_it_can_set_value_of_jsonable_in_model()
    {
        $parser = ModelFileParser::fromFile(__DIR__ . '/../../fixtures/pluginfixture/models/ArrayDataModel.php');
        $parser->setJsonable([
            'field_1',
            'field_2',
        ]);
        $source = $parser->getSource();

        $this->assertStringContainsString('public $jsonable = [\'field_1\', \'field_2\'];', $source);
    }

    public function test_it_can_inject_jsonable_property_in_model_without_it()
    {
        $parser = ModelFileParser::fromFile(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $parser->setJsonable([
            'field_1',
            'field_2',
        ]);
        $source = $parser->getSource();

        $this->assertStringContainsString('public $jsonable = [\'field_1\', \'field_2\'];', $source);
    }
}
