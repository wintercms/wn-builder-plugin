<?php

namespace Winter\Builder\Tests\Unit\Classes;

use Winter\Builder\Classes\MigrationFileParser;
use Winter\Builder\Tests\BuilderPluginTestCase;

/**
 * @covers \Winter\Builder\Classes\MigrationFileParser
 */
class MigrationFileParserTest extends BuilderPluginTestCase
{
   /**
     * @testdox can extract the migration info from an update script.
     */
    public function testIsMigration()
    {
        $firstMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/create_simple_model_table.php'
        );
        $secondMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/anonymous_migration.php'
        );
        $notAMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/not_a_migration.php'
        );

        $this->assertTrue($firstMigration->isMigration());
        $this->assertTrue($secondMigration->isMigration());
        $this->assertFalse($notAMigration->isMigration());
    }

    public function testIsAnonymous()
    {
        $firstMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/create_simple_model_table.php'
        );
        $secondMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/anonymous_migration.php'
        );
        $notAMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/not_a_migration.php'
        );

        $this->assertFalse($firstMigration->isAnonymous());
        $this->assertTrue($secondMigration->isAnonymous());
        $this->assertTrue($notAMigration->isAnonymous());
    }

    public function testGetNamespace()
    {
        $firstMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/create_simple_model_table.php'
        );
        $secondMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/anonymous_migration.php'
        );
        $notAMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/not_a_migration.php'
        );

        $this->assertEquals('Winter\Builder\Tests\Fixtures\PluginFixture\Updates', $firstMigration->getNamespace());
        $this->assertNull($secondMigration->getNamespace());
        $this->assertNull($notAMigration->getNamespace());
    }

    public function testGetClassName()
    {
        $firstMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/create_simple_model_table.php'
        );
        $secondMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/anonymous_migration.php'
        );
        $notAMigration = MigrationFileParser::fromFile(
            __DIR__ . '/../../fixtures/pluginfixture/updates/not_a_migration.php'
        );

        $this->assertEquals('CreateSimpleModelTable', $firstMigration->getClassName());
        $this->assertNull($secondMigration->getClassName());
        $this->assertNull($notAMigration->getClassName());
    }
}
