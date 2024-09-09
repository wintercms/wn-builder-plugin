<?php

namespace Winter\Builder\Classes;

use Illuminate\Database\Migrations\Migration as LaravelMigration;
use Illuminate\Database\Seeder as LaravelSeeder;
use PhpParser\NodeFinder;
use Winter\Storm\Database\Updates\Migration as WinterMigration;
use Winter\Storm\Database\Updates\Seeder as WinterSeeder;

/**
 * Parses migration source files.
 */
class MigrationFileParser extends PhpSourceParser
{
    /**
     * Determines if the given file contains a class that extends a Migration class.
     */
    public function isMigration(): bool
    {
        $class = $this->getClassNode();

        if ($class === null) {
            return false;
        }

        if (is_null($class->extends)) {
            return false;
        }

        if (!in_array($class->extends->toString(), [
            WinterMigration::class,
            LaravelMigration::class,
        ])) {
            return false;
        }

        return true;
    }

    /**
     * Determines if the given file contains a class that extends a Seeder class.
     */
    public function isSeeder(): bool
    {
        $class = $this->getClassNode();

        if ($class === null) {
            return false;
        }

        if (is_null($class->extends)) {
            return false;
        }

        if (!in_array($class->extends->toString(), [
            WinterSeeder::class,
            LaravelSeeder::class,
        ])) {
            return false;
        }

        return true;
    }

    /**
     * Determines if the given source contains an anonymous class definition.
     */
    public function isAnonymous(): bool
    {
        $class = $this->getClassNode();

        if ($class === null) {
            return false;
        }

        return $class->isAnonymous();
    }

    /**
     * Gets the namespace for the migration.
     */
    public function getNamespace(): ?string
    {
        $namespace = $this->getNamespaceNode();

        if ($namespace === null) {
            return null;
        }

        return $namespace->name->toString();
    }

    /**
     * Gets the class name for the migration.
     */
    public function getClassName(): ?string
    {
        $class = $this->getClassNode();

        if ($class === null) {
            return null;
        }

        if ($class->isAnonymous()) {
            return null;
        }

        return $class->name->toString();
    }

    /**
     * Returns the migration namespace and class name.
     * @param string $fileContents Specifies the file contents.
     * @return array|null Returns an array with keys 'class', 'namespace'.
     * Returns null if the parsing fails.
     */
    public function extractMigrationInfoFromSource($fileContents)
    {
        $stream = new PhpSourceStream($fileContents);

        $result = [];

        while ($stream->forward()) {
            $tokenCode = $stream->getCurrentCode();

            if ($tokenCode == T_NAMESPACE) {
                $namespace = $this->extractNamespace($stream);
                if ($namespace === null) {
                    return null;
                }

                $result['namespace'] = $namespace;
            }

            if ($tokenCode == T_CLASS) {
                $className = $this->extractClassName($stream);
                if ($className === null) {
                    return null;
                }

                $result['class'] = $className;
            }
        }

        if (!$result) {
            return null;
        }

        return $result;
    }

    /**
     * Gets the class from the source.
     */
    protected function getClassNode(): ?\PhpParser\Node\Stmt\Class_
    {
        $finder = new NodeFinder;

        return $finder->findFirstInstanceOf($this->ast, \PHPParser\Node\Stmt\Class_::class);
    }

    protected function getNamespaceNode(): ?\PhpParser\Node\Stmt\Namespace_
    {
        $finder = new NodeFinder;

        return $finder->findFirstInstanceOf($this->ast, \PHPParser\Node\Stmt\Namespace_::class);
    }
}
