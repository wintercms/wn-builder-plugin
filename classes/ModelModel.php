<?php namespace Winter\Builder\Classes;

use Str;
use File;
use Lang;
use Schema;
use Validator;
use SystemException;
use DirectoryIterator;
use ApplicationException;

/**
 * Model representation.
 *
 * Manages model classes.
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 * @author Winter CMS
 */
class ModelModel extends BaseModel
{
    /**
     * Regex pattern for the main class name
     */
    const UNQUALIFIED_CLASS_NAME_PATTERN = '/^[A-Z]+[a-zA-Z0-9_]+$/';

    /**
     * @var string|null The class name of the model that this record represents.
     */
    public $className;

    /**
     * @var string|null The database table in use for the model that this record represents.
     */
    public $databaseTable;

    /**
     * @inheritDoc
     */
    protected static $fillable = [
        'className',
        'databaseTable',
        'addTimestamps',
        'addSoftDeleting',
    ];

    /**
     * @inheritDoc
     */
    protected $validationRules = [
        'className' => ['required', 'regex:' . self::UNQUALIFIED_CLASS_NAME_PATTERN, 'uniqModelName'],
        'databaseTable' => ['required'],
        'addTimestamps' => ['timestampColumnsMustExist'],
        'addSoftDeleting' => ['deletedAtColumnMustExist']
    ];

    /**
     * Lists all models available in a particular project.
     *
     * Returns an array of model representations.
     *
     * @param \Winter\Builder\Classes\PluginCode $pluginCodeObj
     * @return static[]
     */
    public static function listPluginModels($pluginCodeObj)
    {
        $modelsDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/models';
        $pluginNamespace = $pluginCodeObj->toPluginNamespace();

        $modelsDirectoryPath = File::symbolizePath($modelsDirectoryPath);
        if (!File::isDirectory($modelsDirectoryPath)) {
            return [];
        }

        $result = [];
        foreach (new DirectoryIterator($modelsDirectoryPath) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if ($fileInfo->getExtension() != 'php') {
                continue;
            }

            $filePath = $fileInfo->getPathname();
            $parser = new ModelFileParser($filePath);

            $modelInfo = $parser->extractModelInfoFromSource();
            if (!$modelInfo) {
                continue;
            }

            if (!Str::startsWith($modelInfo['namespace'], $pluginNamespace.'\\')) {
                continue;
            }

            $model = new ModelModel();
            $model->className = $modelInfo['class'];
            $model->databaseTable = isset($modelInfo['table']) ? $modelInfo['table'] : null;

            $result[] = $model;
        }

        return $result;
    }

    /**
     * Saves the model to the filesystem.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        $modelFilePath = $this->getFilePath();
        $namespace = $this->getPluginCodeObj()->toPluginNamespace().'\\Models';

        $structure = [
            $modelFilePath => 'model.php.tpl'
        ];

        $variables = [
            'namespace' => $namespace,
            'classname' => $this->className,
            'table' => $this->databaseTable
        ];

        $dynamicContents = [];

        $generator = new FilesystemGenerator('$', $structure, '$/winter/builder/classes/modelmodel/templates');
        $generator->setVariables($variables);

        if ($this->addSoftDeleting) {
            $dynamicContents[] = $generator->getTemplateContents('soft-delete.php.tpl');
        }

        if (!$this->addTimestamps) {
            $dynamicContents[] = $generator->getTemplateContents('no-timestamps.php.tpl');
        }

        $generator->setVariable('dynamicContents', implode('', $dynamicContents));

        $generator->generate();
    }

    /**
     * Gets the value of the "$jsonable" attribute
     *
     * @return array|null
     */
    public function getJsonable()
    {
        $parser = new ModelFileParser($this->getFullFilePath());
        return $parser->getJsonable();
    }

    /**
     * Sets the value of the "$jsonable" attribute, and updates the file.
     *
     * @param array $columns
     * @return void
     */
    public function setJsonable(array $columns = [])
    {
        $parser = new ModelFileParser($this->getFullFilePath());
        $parser->setJsonable($columns);

        File::put($this->getFullFilePath(), $parser->getSource());
    }

    /**
     * Validates the model.
     *
     * @return void
     */
    public function validate()
    {
        $path = File::symbolizePath('$/'.$this->getFilePath());

        $this->validationMessages = [
            'className.uniq_model_name' => Lang::get('winter.builder::lang.model.error_class_name_exists', ['path'=>$path]),
            'addTimestamps.timestamp_columns_must_exist' => Lang::get('winter.builder::lang.model.error_timestamp_columns_must_exist'),
            'addSoftDeleting.deleted_at_column_must_exist' => Lang::get('winter.builder::lang.model.error_deleted_at_column_must_exist')
        ];

        Validator::extend('uniqModelName', function ($attribute, $value, $parameters) use ($path) {
            $value = trim($value);

            if (!$this->isNewModel()) {
                // Editing models is not supported at the moment,
                // so no validation is required.
                return true;
            }

            return !File::isFile($path);
        });

        $columns = $this->isNewModel() ? Schema::getColumnListing($this->databaseTable) : [];
        Validator::extend('timestampColumnsMustExist', function ($attribute, $value, $parameters) use ($columns) {
            return $this->validateColumnsExist($value, $columns, ['created_at', 'updated_at']);
        });

        Validator::extend('deletedAtColumnMustExist', function ($attribute, $value, $parameters) use ($columns) {
            return $this->validateColumnsExist($value, $columns, ['deleted_at']);
        });

        parent::validate();
    }

    /**
     * Deletes the model from the filesystem.
     *
     * This will delete any model assets for the given model, as well.
     *
     * @return void
     */
    public function deleteModel()
    {
        if (File::exists($this->getFullFilePath())) {
            File::delete($this->getFullFilePath());
        }

        if (File::exists($this->getFullAssetPath())) {
            File::deleteDirectory($this->getFullAssetPath());
        }
    }

    /**
     * Gets a list of database tables.
     *
     * @return array
     */
    public function getDatabaseTableOptions()
    {
        $pluginCode = $this->getPluginCodeObj()->toCode();

        $tables = array_map(function ($item) {
            return $item['table'];
        }, DatabaseTableModel::listPluginTables($pluginCode));

        return array_combine($tables, $tables);
    }

    /**
     * Returns the table name defined in a plugin's model.
     *
     * If table name is unable to be determined, an empty string will be returned.
     *
     * @param \Winter\Builder\Classes\PluginCode $pluginCodeObj
     * @param string $modelClassName
     * @return string
     */
    private static function getTableNameFromModelClass($pluginCodeObj, $modelClassName)
    {
        if (!self::validateModelClassName($modelClassName)) {
            throw new SystemException('Invalid model class name: '.$modelClassName);
        }

        $modelsDirectoryPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/models');
        if (!File::isDirectory($modelsDirectoryPath)) {
            return '';
        }

        $modelFilePath = $modelsDirectoryPath.'/'.$modelClassName.'.php';
        if (!File::isFile($modelFilePath)) {
            return '';
        }

        $parser = new ModelFileParser($modelFilePath);
        $modelInfo = $parser->extractModelInfoFromSource();
        if (!$modelInfo || !isset($modelInfo['table'])) {
            return '';
        }

        return $modelInfo['table'];
    }

    /**
     * Gets the table columns for a given plugin's model.
     *
     * @param \Winter\Builder\Classes\PluginCode $pluginCodeObj
     * @param string $modelClassName
     * @return array
     */
    public static function getModelFields($pluginCodeObj, $modelClassName)
    {
        $tableName = self::getTableNameFromModelClass($pluginCodeObj, $modelClassName);

        // Currently we return only table columns,
        // but eventually we might want to return relations as well.

        return Schema::getColumnListing($tableName);
    }

    /**
     * Gets the table columns and types for a given plugin's model.
     *
     * @param \Winter\Builder\Classes\PluginCode $pluginCodeObj
     * @param string $modelClassName
     * @return array
     */
    public static function getModelColumnsAndTypes($pluginCodeObj, $modelClassName)
    {
        $tableName = self::getTableNameFromModelClass($pluginCodeObj, $modelClassName);

        if (!DatabaseTableModel::tableExists($tableName)) {
            throw new ApplicationException('Database table not found: '.$tableName);
        }

        $schema = DatabaseTableModel::getSchema();
        $tableInfo = $schema->getTable($tableName);

        $columns = $tableInfo->getColumns();
        $result = [];
        foreach ($columns as $column) {
            $columnName = $column->getName();
            $typeName = $column->getType()->getName();

            if ($typeName == EnumDbType::TYPENAME) {
                continue;
            }

            $item = [
                'name' => $columnName,
                'type' => MigrationColumnType::toMigrationMethodName($typeName, $columnName)
            ];

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Returns a list of fully qualified model classes mapped to their own class name within a plugin.
     *
     * @TODO: Remove the $subType parameter - it's not used.
     *
     * @param string $pluginCode
     * @param string $subtype
     * @return array
     */
    public static function getPluginRegistryData($pluginCode, $subtype)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $models = self::listPluginModels($pluginCodeObj);
        $result = [];
        foreach ($models as $model) {
            $fullClassName = $pluginCodeObj->toPluginNamespace().'\\Models\\'.$model->className;

            $result[$fullClassName] = $model->className;
        }

        return $result;
    }

    /**
     * Returns a list of table columns for a given plugin's model.
     *
     * @param string $pluginCode
     * @param string $modelClassName
     * @return array
     */
    public static function getPluginRegistryDataColumns($pluginCode, $modelClassName)
    {
        $classParts = explode('\\', $modelClassName);
        if (!$classParts) {
            return [];
        }

        $modelClassName = array_pop($classParts);

        if (!self::validateModelClassName($modelClassName)) {
            return [];
        }

        $pluginCodeObj = new PluginCode($pluginCode);
        $columnNames = self::getModelFields($pluginCodeObj, $modelClassName);

        $result = [];
        foreach ($columnNames as $columnName) {
            $result[$columnName] = $columnName;
        }

        return $result;
    }

    /**
     * Validates the model class name.
     *
     * @param string $modelClassName
     * @return bool
     */
    public static function validateModelClassName($modelClassName)
    {
        return class_exists($modelClassName) || !!preg_match(self::UNQUALIFIED_CLASS_NAME_PATTERN, $modelClassName);
    }

    /**
     * Gets the file path to the model, relative to the "plugin" directory.
     *
     * @return string
     */
    protected function getFilePath()
    {
        return $this->getPluginCodeObj()->toFilesystemPath() . '/models/' . $this->className . '.php';
    }

    /**
     * Gets the absolute file path to the model.
     *
     * @return string
     */
    protected function getFullFilePath()
    {
        return File::symbolizePath('$/' . $this->getFilePath());
    }

    /**
     * Gets the file path to the model assets, relative to the "plugin" directory.
     *
     * @return string
     */
    protected function getAssetPath()
    {
        return $this->getPluginCodeObj()->toFilesystemPath() . '/models/' . strtolower($this->className);
    }

    /**
     * Gets the absolute file path to the model assets.
     *
     * @return string
     */
    protected function getFullAssetPath()
    {
        return File::symbolizePath('$/' . $this->getAssetPath());
    }

    /**
     * Validates that columns exist for a given column set.
     *
     * @TODO: Remove the $value property - seems to be useless.
     *
     * @param mixed $value
     * @param array $columns
     * @param array $columnsToCheck
     * @return bool
     */
    protected function validateColumnsExist($value, $columns, $columnsToCheck)
    {
        if (!strlen(trim($this->databaseTable))) {
            return true;
        }

        if (!$this->isNewModel()) {
            // Editing models is not supported at the moment,
            // so no validation is required.
            return true;
        }

        if (!$value) {
            return true;
        }

        return count(array_intersect($columnsToCheck, $columns)) == count($columnsToCheck);
    }
}
