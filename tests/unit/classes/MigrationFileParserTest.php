<?php

namespace Winter\Builder\Tests\Unit\Classes;

use PHPUnit\Framework\Attributes\CoversClass;
use Winter\Builder\Classes\MigrationFileParser;
use Winter\Builder\Tests\BuilderPluginTestCase;

#[CoversClass(MigrationFileParser::class)]
class MigrationFileParserTest extends BuilderPluginTestCase
{
    public function test_it_can_determine_if_file_is_class_migration()
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

    public function test_it_can_determine_if_file_is_anonymous_migration()
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

    public function test_it_can_get_namespace_of_class()
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

    public function test_it_can_get_class_name_of_class()
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
