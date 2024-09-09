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
     * @testdox can determine if a file is a migration class definition.
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

   /**
     * @testdox can determine if a file is an anonymous class definition.
     */
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

   /**
     * @testdox can get the namespace of a class.
     */
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

   /**
     * @testdox can get the class name of a class.
     */
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
