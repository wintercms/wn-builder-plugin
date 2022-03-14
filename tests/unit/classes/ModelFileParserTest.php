<?php namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\ModelFileParser;

/**
 * @covers \Winter\Builder\Classes\ModelFileParser
 */
class ModelFileParserTest extends \TestCase
{
    public function testExtractModelInfoFromSource()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $modelInfo = $parser->extractModelInfoFromSource();

        $this->assertEquals('Winter\Builder\Tests\Fixtures\PluginFixture\Models', $modelInfo['namespace']);
        $this->assertEquals('SimpleModel', $modelInfo['class']);
        $this->assertEquals('plugin_fixture_simple_model', $modelInfo['table']);
    }

    public function testGetSource()
    {
        $parser = new ModelFileParser(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php');
        $source = $parser->getSource();

        $this->assertEquals(file_get_contents(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php'), $source);
    }

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
