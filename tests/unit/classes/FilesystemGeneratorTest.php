<?php namespace Winter\Builder\Tests\Unit\Classes;

use File;
use Winter\Builder\Classes\FilesystemGenerator;
use Winter\Builder\Tests\BuilderPluginTestCase;

class FilesystemGeneratorTest extends BuilderPluginTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();
    }

    public function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testGenerate()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        $structure = [
            'author',
            'author/plugin',
            'author/plugin/plugin.php' => 'plugin.php.tpl',
            'author/plugin/classes'
        ];

        $templatesDir = $this->getFixturesDir('templates');
        $generator = new FilesystemGenerator($generatedDir, $structure, $templatesDir);

        $variables = [
            'authorNamespace' => 'Author',
            'pluginNamespace' => 'Plugin'
        ];
        $generator->setVariables($variables);
        $generator->setVariable('className', 'TestClass');

        $generator->generate();

        $this->assertFileExists($generatedDir.'/author/plugin/plugin.php');
        $this->assertFileExists($generatedDir.'/author/plugin/classes');

        $content = file_get_contents($generatedDir.'/author/plugin/plugin.php');
        $this->assertStringContainsString('Author\Plugin', $content);
        $this->assertStringContainsString('TestClass', $content);
    }

    public function testDestNotExistsException()
    {
        $this->expectException(\Winter\Storm\Exception\SystemException::class);
        $this->expectExceptionMessageMatches('/exists/');

        $dir = $this->getFixturesDir('temporary/null');
        $generator = new FilesystemGenerator($dir, []);
        $generator->generate();
    }

    public function testDirExistsException()
    {
        $this->expectException(\Winter\Storm\Exception\ApplicationException::class);
        $this->expectExceptionMessageMatches('/exists/');

        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir.'/plugin', 0777, true, true);
        $this->assertFileExists($generatedDir.'/plugin');

        $structure = [
            'plugin'
        ];

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    public function testFileExistsException()
    {
        $this->expectException(\Winter\Storm\Exception\ApplicationException::class);
        $this->expectExceptionMessageMatches('/exists/');

        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        File::put($generatedDir.'/plugin.php', 'contents');
        $this->assertFileExists($generatedDir.'/plugin.php');

        $structure = [
            'plugin.php' => 'plugin.php.tpl'
        ];

        $generator = new FilesystemGenerator($generatedDir, $structure);
        $generator->generate();
    }

    public function testTemplateNotFound()
    {
        $this->expectException(\Winter\Storm\Exception\SystemException::class);
        $this->expectExceptionMessageMatches('/found/');

        $generatedDir = $this->getFixturesDir('temporary/generated');
        $this->assertFileNotExists($generatedDir);

        File::makeDirectory($generatedDir, 0777, true, true);
        $this->assertFileExists($generatedDir);

        $structure = [
            'plugin.php' => 'null.tpl'
        ];

        $templatesDir = $this->getFixturesDir('templates');

        $generator = new FilesystemGenerator($generatedDir, $structure, $templatesDir);
        $generator->generate();
    }

    protected function getFixturesDir($subdir)
    {
        $result = __DIR__.'/../../fixtures/filesystemgenerator';

        if (strlen($subdir)) {
            $result .= '/'.$subdir;
        }

        return $result;
    }

    protected function cleanUp()
    {
        $generatedDir = $this->getFixturesDir('temporary/generated');
        File::deleteDirectory($generatedDir);
    }
}
