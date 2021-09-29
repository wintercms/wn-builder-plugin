<?php namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\ModelFileParser;

/**
 * @covers \Winter\Builder\Classes\ModelFileParser
 */
class ModelFileParserTest extends \TestCase
{
    /**
     * ModelFileParser instance
     *
     * @var ModelFileParser
     */
    protected $modelFileParser;

    public function setUp(): void
    {
        $this->modelFileParser = new ModelFileParser();
    }

    public function testExtractModelInfoFromSource()
    {
        $modelInfo = $this->modelFileParser->extractModelInfoFromSource(
            file_get_contents(__DIR__ . '/../../fixtures/pluginfixture/models/SimpleModel.php')
        );

        $this->assertEquals('Winter\Builder\Tests\Fixtures\PluginFixture\Models', $modelInfo['namespace']);
        $this->assertEquals('SimpleModel', $modelInfo['class']);
        $this->assertEquals('plugin_fixture_simple_model', $modelInfo['table']);
    }
}
