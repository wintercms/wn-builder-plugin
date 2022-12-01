<?php namespace Winter\Builder\Classes;

use File;
use ApplicationException;
use PhpParser\Error;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * PHP File Parser.
 *
 * Simplifies modifications and parsing of PHP files, using the "nikic/php-parser" library.
 *
 * @author Winter CMS
 */
abstract class PhpFileParser
{
    /**
     * @var \PhpParser\Node\Stmt[] The abstract syntax tree of the given PHP file, cloned for manipulation.
     */
    protected $ast;

    /**
     * @var array Tokens from the given PHP file, used for manipulation.
     */
    protected $originalTokens;

    /**
     * @var \PhpParser\Node\Stmt[] The abstract syntax tree of the given PHP file, as originally defined.
     */
    protected $originalAst;

    /**
     * @var \PhpParser\NodeTraverser The traverser used for add context for nodes.
     */
    protected $traverser;

    /**
     * @var \PhpParser\Lexer\Emulative The lexer used to manipulate code.
     */
    protected $lexer;

    /**
     * @var string The path to the parsed file.
     */
    protected $filePath;

    /**
     * Constructor.
     *
     * Automatically parses the given file.
     */
    public function __construct(string $file)
    {
        if (!File::exists($file) || File::extension($file) !== 'php') {
            throw new ApplicationException(
                sprintf('File "%s" is not a valid PHP file to parse.', $file)
            );
        }

        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $this->lexer);

        try {
            $this->originalAst = $parser->parse(File::get($file));
        } catch (Error $e) {
            throw new ApplicationException(
                sprintf('Unable to parse PHP file "%s". (%s)', $file, $e->getMessage())
            );
        }

        $this->originalTokens = $this->lexer->getTokens();
        $this->traverser = new NodeTraverser;
        $this->traverser->addVisitor(new CloningVisitor);
        $this->ast = $this->traverser->traverse($this->originalAst);

        $this->filePath = $file;
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
