<?php

namespace Winter\Builder\Classes;

use PhpParser\Error;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\File;

/**
 * PHP File Parser.
 *
 * Simplifies modifications and parsing of PHP files, using the "nikic/php-parser" library.
 *
 * @author Winter CMS
 */
abstract class PhpSourceParser
{
    /**
     * @var \PhpParser\Node\Stmt[] The abstract syntax tree of the given PHP file, cloned for manipulation.
     */
    protected array $ast;

    /**
     * @var array Tokens from the given PHP file, used for manipulation.
     */
    protected array $originalTokens;

    /**
     * @var \PhpParser\Node\Stmt[] The abstract syntax tree of the given PHP file, as originally defined.
     */
    protected array $originalAst;

    /**
     * The traverser used for add context for nodes.
     */
    protected \PhpParser\NodeTraverser $traverser;

    /**
     * The lexer used to manipulate code.
     */
    protected \PhpParser\Lexer\Emulative $lexer;

    /**
     * The path to the parsed file, if loaded from a file.
     */
    protected ?string $filePath = null;

    /**
     * Constructor.
     *
     * Automatically parses the given source.
     */
    public function __construct(string $source, string $file = null)
    {
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $this->lexer);

        try {
            $this->originalAst = $parser->parse($source);
        } catch (Error $e) {
            throw new ApplicationException(
                (!is_null($file))
                    ? sprintf('Unable to parse the PHP source in file "%s". (%s)', $file, $e->getMessage())
                    : sprintf('Unable to parse the PHP source. (%s)', $e->getMessage())
            );
        }

        $this->originalTokens = $this->lexer->getTokens();
        $this->traverser = new NodeTraverser;
        $this->traverser->addVisitor(new CloningVisitor);
        $this->traverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
            'replaceNodes' => true,
        ]));
        $this->ast = $this->traverser->traverse($this->originalAst);
        $this->filePath = $file;
    }

    /**
     * Parses a PHP file.
     */
    public static function fromFile(string $file)
    {
        if (!File::exists($file) || File::extension($file) !== 'php') {
            throw new ApplicationException(
                sprintf('File "%s" is not a valid PHP file to parse.', $file)
            );
        }

        return new static(File::get($file), $file);
    }

    /**
     * Returns the pretty-printed source based off the cloned AST.
     *
     * @return string
     */
    public function getSource()
    {
        $printer = new Standard();

        return $printer->printFormatPreserving($this->ast, $this->originalAst, $this->originalTokens);
    }
}
