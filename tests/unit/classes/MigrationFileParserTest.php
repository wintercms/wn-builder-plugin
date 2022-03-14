<?php namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\MigrationFileParser;

/**
 * @testdox Migration File Parser
 * @covers \Winter\Builder\Classes\MigrationFileParser
 */
class MigrationFileParserTest extends \TestCase
{
    /**
     * MigrationFileParser instance
     *
     * @var MigrationFileParser
     */
    protected $migrationFileParser;

    public function setUp(): void
    {
        $this->migrationFileParser = new MigrationFileParser();
    }

    /**
     * @testdox can extract the migration info from an update script.
     */
    public function testExtractMigrationInfoFromSource()
    {
        $migrationInfo = $this->migrationFileParser->extractMigrationInfoFromSource(
            file_get_contents(__DIR__ . '/../../fixtures/pluginfixture/updates/create_simple_model_table.php')
        );

        $this->assertNotNull($migrationInfo);
        $this->assertEquals('Winter\Builder\Tests\Fixtures\PluginFixture\Updates', $migrationInfo['namespace']);
        $this->assertEquals('CreateSimpleModelTable', $migrationInfo['class']);
    }
}
