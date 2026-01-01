<?php namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\DatabaseTableModel;
use Backend\Behaviors\FormController;
use Winter\Builder\Classes\MigrationModel;
use Winter\Builder\Classes\TableMigrationCodeGenerator;
use Winter\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Input;
use Lang;

/**
 * Database table management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexDatabaseTableOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/databasetablemodel/fields.yaml';
    protected $migrationFormConfigFile = '~/plugins/winter/builder/classes/migrationmodel/fields.yaml';

    public function onDatabaseTableCreateOrOpen()
    {
        $tableName = Input::get('table_name');
        $pluginCodeObj = $this->getPluginCode();

        $widget = $this->makeBaseFormWidget($tableName);
        $this->vars['tableName'] = $tableName;

        $result = [
            'tabTitle' => $this->getTabTitle($tableName),
            'tabIcon' => 'icon-database',
            'tabId' => $this->getTabId($tableName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'tableName' => $tableName
            ])
        ];

        return $result;
    }

    public function onDatabaseTableValidateAndShowPopup()
    {
        $tableName = Input::get('table_name');

        $model = $this->loadOrCreateBaseModel($tableName);
        $model->fill($this->processColumnData($_POST));

        $pluginCode = Request::input('plugin_code');
        $model->setPluginCode($pluginCode);
        try {
            $model->validate();
        } catch (Exception $ex) {
            throw new ApplicationException($ex->getMessage());
        }

        $migration = $model->generateCreateOrUpdateMigration();

        if (!$migration) {
            return $this->makePartial('migration-popup-form', [
                'noChanges' => true
            ]);
        }

        return $this->makePartial('migration-popup-form', [
            'form' => $this->makeMigrationFormWidget($migration),
            'operation' => $model->isNewModel() ? 'create' : 'update',
            'table' => $model->name,
            'pluginCode' => $pluginCode
        ]);
    }

    public function onDatabaseTableMigrationApply()
    {
        $pluginCode = new PluginCode(Request::input('plugin_code'));
        $model = new MigrationModel();
        $model->setPluginCodeObj($pluginCode);

        // Fill all fields from the form - code is already wrapped from DatabaseTableModel
        $model->fill([
            'version' => Request::input('version'),
            'description' => Request::input('description'),
            'code' => Request::input('code'),
        ]);

        // The scriptFileName should be extracted from the code by MigrationModel::assignFileName()
        // But as a safety fallback, generate it if needed
        $operation = Input::get('operation');
        $table = Input::get('table');

        if (!$model->scriptFileName) {
            $model->scriptFileName = 'builder_table_'.$operation.'_'.$table;
            $model->makeScriptFileNameUnique();
        }

        try {
            $model->save();
        } catch (Exception $ex) {
            throw new ApplicationException($ex->getMessage());
        }

        $result = $this->controller->widget->databaseTableList->updateList();

        $result = array_merge(
            $result,
            $this->controller->widget->versionList->refreshActivePlugin()
        );

        if ($operation === 'delete') {
            $result['builderResponseData'] = [
                'builderObjectName' => $table,
                'tabId' => $this->getTabId($table),
                'tabTitle' => $table,
                'tableName' => $table,
                'operation' => $operation,
                'pluginCode' => $pluginCode->toCode()
            ];
        } else {
            $widget = $this->makeBaseFormWidget($table);
            $this->vars['tableName'] = $table;

            $result['builderResponseData'] = [
                'builderObjectName' => $table,
                'tabId' => $this->getTabId($table),
                'tabTitle' => $table,
                'tableName' => $table,
                'operation' => $operation,
                'pluginCode' => $pluginCode->toCode(),
                'tab' => $this->makePartial('tab', [
                    'form'  => $widget,
                    'pluginCode' => $this->getPluginCode()->toCode(),
                    'tableName' => $table
                ])
            ];
        }

        return $result;
    }

    public function onDatabaseTableShowDeletePopup()
    {
        $tableName = Input::get('table_name');

        $model = $this->loadOrCreateBaseModel($tableName);
        $pluginCode = Request::input('plugin_code');
        $model->setPluginCode($pluginCode);

        $migration = $model->generateDropMigration();

        return $this->makePartial('migration-popup-form', [
            'form' => $this->makeMigrationFormWidget($migration),
            'operation' => 'delete',
            'table' => $model->name,
            'pluginCode' => $pluginCode
        ]);
    }

    protected function getTabTitle($tableName)
    {
        if (!strlen($tableName)) {
            return Lang::get('winter.builder::lang.database.tab_new_table');
        }

        return $tableName;
    }

    protected function getTabId($tableName)
    {
        if (!strlen($tableName)) {
            return 'databaseTable-'.uniqid(time());
        }

        return 'databaseTable-'.$tableName;
    }

    protected function loadOrCreateBaseModel($tableName, $options = [])
    {
        $model = new DatabaseTableModel();

        if (!$tableName) {
            $model->name = $this->getPluginCode()->toDatabasePrefix().'_';

            return $model;
        }

        $model->load($tableName);
        return $model;
    }

    protected function makeMigrationFormWidget($migration, $alias = null)
    {
        $widgetConfig = $this->makeConfig($this->migrationFormConfigFile);

        $widgetConfig->model = $migration;
        $widgetConfig->alias = $alias ?: 'form_migration_'.uniqid().'_';

        $form = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
        $form->context = FormController::CONTEXT_CREATE;

        return $form;
    }

    /**
     * Reconstructs a migration FormWidget from POST data
     *
     * This allows widgets within popups (like CodeEditor) to make AJAX requests
     * by rebuilding the FormWidget state from the request data
     *
     * @param string $alias The exact widget alias to recreate
     * @return \Backend\Widgets\Form|null The reconstructed form widget, or null if not a migration form
     */
    protected function reconstructFormWidget($alias)
    {
        // Only handle migration forms (popup forms for database table operations)
        if (!preg_match('/^form_migration_[a-z0-9]+_$/i', $alias)) {
            return parent::reconstructFormWidget($alias);
        }

        // Get plugin code from request or fall back to active plugin
        $pluginCode = Request::input('plugin_code');
        if (!$pluginCode) {
            $pluginCode = $this->getPluginCode()->toCode();
        }

        // Build MigrationModel from POST data
        $migration = new MigrationModel();
        $migration->setPluginCodeObj(new PluginCode($pluginCode));
        $migration->fill([
            'version' => Request::input('version', ''),
            'description' => Request::input('description', ''),
            'code' => Request::input('code', '')
        ]);

        // Create FormWidget with exact same config as original popup
        // Using the EXACT alias from the AJAX handler is critical
        $form = $this->makeMigrationFormWidget($migration, $alias);

        // CRITICAL: Bind to controller so it's available in $this->widget
        // This makes the widget discoverable when the AJAX handler looks it up
        $form->bindToController();

        return $form;
    }

    protected function processColumnData($postData)
    {
        if (!array_key_exists('columns', $postData)) {
            return $postData;
        }

        $booleanColumns = ['unsigned', 'allow_null', 'auto_increment', 'primary_key'];
        foreach ($postData['columns'] as &$row) {
            foreach ($row as $column => $value) {
                if (in_array($column, $booleanColumns) && $value == 'false') {
                    $row[$column] = false;
                }
            }
        }

        return $postData;
    }
}
