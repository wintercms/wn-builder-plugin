<?php namespace Winter\Builder\Classes;

use ApplicationException;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;
use PhpParser\NodeFinder;

/**
 * Parses and manipulates model files.
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 * @author Winter CMS
 */
class ModelFileParser extends PhpFileParser
{
    /**
     * Returns the model namespace, class name and table name.
     *
     * @return array|null Returns an array with keys 'namespace', 'class' and 'table'. Returns `null` if the parsing
     *  fails.
     */
    public function extractModelInfoFromSource()
    {
        $result = [];
        $finder = new NodeFinder;

        // Get the namespace
        $namespace = $finder->findFirstInstanceOf($this->ast, Namespace_::class);

        if ($namespace === null) {
            return null;
        }

        $result['namespace'] = $namespace->name->toString();

        // Get the class name
        $class = $finder->findFirstInstanceOf($this->ast, Class_::class);

        if ($class === null) {
            return null;
        }

        $result['class'] = $class->name->toString();
        $result['fqClass'] = Name::concat($namespace->name, $class->name->toString());

        // Get the table name
        $tableName = $finder->findFirst($class, function (Node $node) {
            return (
                $node instanceof PropertyProperty
                && $node->name instanceof VarLikeIdentifier
                && $node->name->name === 'table'
            );
        });

        if (
            $tableName === null
            || (
                $tableName->default instanceof String_ === false
                && $tableName->default instanceof Bool_ === false
            )
        ) {
            return null;
        }

        if ($tableName->default instanceof String_) {
            $result['table'] = $tableName->default->value;
        }

        return $result;
    }

    /**
     * Gets the value of the $jsonable property, if available.
     *
     * @return array|null
     */
    public function getJsonable()
    {
        $finder = new NodeFinder;

        $class = $finder->findFirstInstanceOf($this->ast, Class_::class);

        if ($class === null) {
            return null;
        }

        $jsonable = $finder->findFirst($class, function (Node $node) {
            return (
                $node instanceof PropertyProperty
                && $node->name instanceof VarLikeIdentifier
                && $node->name->name === 'jsonable'
            );
        });

        if ($jsonable === null || $jsonable->default instanceof Array_ === false) {
            return null;
        }

        $columns = [];

        foreach ($jsonable->default->items as $item) {
            if ($item->value instanceof String_) {
                $columns[] = $item->value->value;
            } elseif (method_exists($item->value, 'toString')) {
                $columns[] = $item->value->toString();
            }
        }

        return $columns;
    }

    /**
     * Sets the value of the $jsonable property for a model.
     *
     * If the property does not exist, it will be added.
     *
     * @param array $columns
     * @return void
     * @throws ApplicationException If the property cannot be created, ie. this is not a class.
     */
    public function setJsonable(array $columns)
    {
        $finder = new NodeFinder;

        $class = $finder->findFirstInstanceOf($this->ast, Class_::class);

        if ($class === null) {
            throw new ApplicationException(
                sprintf(
                    'File "%s" does not appear to be a class, and cannot have the $jsonable property written',
                    $this->filePath
                )
            );
        }

        $jsonable = $finder->findFirst($class, function (Node $node) {
            return (
                $node instanceof PropertyProperty
                && $node->name instanceof VarLikeIdentifier
                && $node->name->name === 'jsonable'
            );
        });

        if ($jsonable === null) {
            // We must create the property - we'll need to traverse the property list, find the first method definition, and
            // add $jsonable before it

            // Create an array of items
            $arrayItems = [];
            foreach ($columns as $column) {
                $arrayItems[] = new ArrayItem(
                    new String_($column)
                );
            }

            // Create a property
            $property = new Property(
                Class_::MODIFIER_PUBLIC,
                [
                    new PropertyProperty(
                        new VarLikeIdentifier('jsonable'),
                        new Array_($arrayItems, [
                            'kind' => Array_::KIND_SHORT
                        ])
                    )
                ]
            );

            // Set the docblock for the property
            $property->setDocComment(new Doc(
                "\n"
                . "/**\n"
                . " * @var array Attribute names to encode and decode using JSON.\n"
                . " */"
            ));

            $firstMethodIndex = null;

            foreach ($class->stmts as $i => $stmt) {
                if ($stmt instanceof ClassMethod) {
                    $firstMethodIndex = $i;
                    break;
                }
            }

            if ($firstMethodIndex !== null) {
                array_splice(
                    $class->stmts,
                    $firstMethodIndex,
                    0,
                    [$property]
                );
                return;
            }

            $class->stmts[] = $property;
            return;
        }

        // Reset $jsonable property and add new columns
        $jsonable->default->items = [];

        foreach ($columns as $column) {
            $jsonable->default->items[] = new ArrayItem(
                new String_($column)
            );
        }
    }
}
