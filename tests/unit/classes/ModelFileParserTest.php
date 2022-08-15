<?php namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\ModelFileParser;
use Winter\Builder\Tests\BuilderPluginTestCase;

/**
 * @covers \Winter\Builder\Classes\ModelFileParser
 */
class ModelFileParserTest extends BuilderPluginTestCase
{
    /**
     * @testdox can get information for the model from the parser.
     */
    public function testExtractModelInfoFromSource()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $modelInfo = $parser->extractModelInfoFromSource();

        $this->assertEquals('Winter\Builder\Tests\Fixtures\PluginFixture\Models', $modelInfo['namespace']);
        $this->assertEquals('SimpleModel', $modelInfo['class']);
        $this->assertEquals('plugin_fixture_simple_model', $modelInfo['table']);
    }

    /**
     * @testdox can generate and provide the source code of a given model from the parser.
     */
    public function testGetSource()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $source = $parser->getSource();

        $this->assertEquals(file_get_contents(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php'), $source);
    }

    /**
     * @testdox can get the value of the $jsonable property from a model.
     */
    public function testGetJsonable()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $jsonable = $parser->getJsonable();
        $this->assertNull($jsonable);

        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/ArrayDataModel.php');
        $jsonable = $parser->getJsonable();
        $this->assertEquals([
            'data',
        ], $jsonable);
    }

    /**
     * @testdox can set the value of the $jsonable property in a model.
     */
    public function testSetJsonable()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/ArrayDataModel.php');
        $parser->setJsonable([
            'field_1',
            'field_2',
        ]);
        $source = $parser->getSource();

        $this->assertStringContainsString('public $jsonable = [\'field_1\', \'field_2\'];', $source);
    }

    /**
     * @testdox can inject the $jsonable property in a model that does not contain it.
     */
    public function testSetJsonableAddProperty()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $parser->setJsonable([
            'field_1',
            'field_2',
        ]);
        $source = $parser->getSource();

        $this->assertStringContainsString('public $jsonable = [\'field_1\', \'field_2\'];', $source);
    }
}
