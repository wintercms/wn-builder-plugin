<?php namespace Winter\Builder\Behaviors;

use Lang;
use Flash;
use Request;
use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\ModelFormModel;
use Winter\Builder\FormWidgets\FormBuilder;
use Winter\Builder\Classes\ModelModel;
use Winter\Builder\Classes\ControlLibrary;
use Backend\Classes\FormField;
use Backend\FormWidgets\DataTable;

/**
 * Model form management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexModelFormOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/modelformmodel/fields.yaml';

    public function __construct($controller)
    {
        parent::__construct($controller);

        // Create the form builder instance to handle AJAX
        // requests.
        $defaultBuilderField = new FormField('default', 'default');
        $formBulder = new FormBuilder($controller, $defaultBuilderField);
        $formBulder->alias = 'defaultFormBuilder';
        $formBulder->bindToController();
    }

    public function onModelFormCreateOrOpen()
    {
        $fileName = Request::input('file_name');
        $modelClass = Request::input('model_class');

        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode(),
            'modelClass' => $modelClass
        ];

        $widget = $this->makeBaseFormWidget($fileName, $options);
        $this->vars['fileName'] = $fileName;

        $result = [
            'tabTitle' => $widget->model->getDisplayName(Lang::get('winter.builder::lang.form.tab_new_form')),
            'tabIcon' => 'icon-check-square',
            'tabId' => $this->getTabId($modelClass, $fileName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'fileName' => $fileName,
                'modelClass' => $modelClass
            ])
        ];

        return $result;
    }

    public function onModelFormSave()
    {
        $model = $this->loadOrCreateFormFromPost();
        $originalJsonable = $model->getJsonableFields();
        $model->fill($_POST);
        $newJsonable = $model->getJsonableFields();
        $model->save();

        // Diff JSONable fields and store these in the main model, keeping any changes added by the user
        $modelClass = new ModelModel();
        $modelClass->setPluginCode(Request::input('plugin_code'));
        $modelClass->className = Request::input('model_class');
        $jsonable = $this->updateJsonable($modelClass->getJsonable(), $originalJsonable, $newJsonable);
        $modelClass->setJsonable($jsonable);

        $result = $this->controller->widget->modelList->updateList();

        Flash::success(Lang::get('winter.builder::lang.form.saved'));

        $modelClass = Request::input('model_class');
        $result['builderResponseData'] = [
            'builderObjectName' => $model->fileName,
            'tabId' => $this->getTabId($modelClass, $model->fileName),
            'tabTitle' => $model->getDisplayName(Lang::get('winter.builder::lang.form.tab_new_form'))
        ];

        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    public function onModelFormDelete()
    {
        $model = $this->loadOrCreateFormFromPost();

        $model->deleteModel();

        $result = $this->controller->widget->modelList->updateList();

        $modelClass = Request::input('model_class');
        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    public function onModelFormGetModelFields()
    {
        $columnNames = ModelModel::getModelFields($this->getPluginCode(), Request::input('model_class'));
        $asPlainList = Request::input('as_plain_list');

        $result = [];
        foreach ($columnNames as $columnName) {
            if (!$asPlainList) {
                $result[] = [
                    'title' => $columnName,
                    'value' => $columnName
                ];
            }
            else {
                $result[$columnName] = $columnName;
            }
        }

        return [
            'responseData' => [
                'options' => $result
            ]
        ];
    }

    public function onModelShowAddDatabaseFieldsPopup()
    {
        $columns = ModelModel::getModelColumnsAndTypes($this->getPluginCode(), Request::input('model_class'));
        $config = $this->makeConfig($this->getAddDatabaseFieldsDataTableConfig());

        $field = new FormField('add_database_fields_datatable', 'add_database_fields_datatable');
        $field->value = $this->getAddDatabaseFieldsDataTableValue($columns);

        $datatable = new DataTable($this->controller, $field, $config);
        $datatable->alias = 'add_database_fields_datatable';
        $datatable->bindToController();

        return $this->makePartial('add-database-fields-popup-form', [
            'datatable'  => $datatable,
            'pluginCode' => $this->getPluginCode()->toCode(),
        ]);
    }

    protected function loadOrCreateFormFromPost()
    {
        $pluginCode = Request::input('plugin_code');
        $modelClass = Request::input('model_class');
        $fileName = Request::input('file_name');

        $options = [
            'pluginCode' => $pluginCode,
            'modelClass' => $modelClass
        ];

        return $this->loadOrCreateBaseModel($fileName, $options);
    }

    protected function getTabId($modelClass, $fileName)
    {
        if (!strlen($fileName)) {
            return 'modelForm-'.uniqid(time());
        }

        return 'modelForm-'.$modelClass.'-'.$fileName;
    }

    protected function loadOrCreateBaseModel($fileName, $options = [])
    {
        $model = new ModelFormModel();

        if (isset($options['pluginCode']) && isset($options['modelClass'])) {
            $model->setPluginCode($options['pluginCode']);
            $model->setModelClassName($options['modelClass']);
        }

        if (!$fileName) {
            $model->initDefaults();

            return $model;
        }

        $model->loadForm($fileName);
        return $model;
    }

    protected function mergeRegistryDataIntoResult(&$result, $model, $modelClass)
    {
        if (!array_key_exists('builderResponseData', $result)) {
            $result['builderResponseData'] = [];
        }

        $fullClassName = $model->getPluginCodeObj()->toPluginNamespace().'\\Models\\'.$modelClass;
        $pluginCode = $model->getPluginCodeObj()->toCode();
        $result['builderResponseData']['registryData'] = [
            'forms' => ModelFormModel::getPluginRegistryData($pluginCode, $modelClass),
            'pluginCode' => $pluginCode,
            'modelClass' => $fullClassName
        ];
    }

    /**
     * Returns the configuration for the DataTable widget that
     * is used in the "add database fields" popup.
     *
     * @return array
     */
    protected function getAddDatabaseFieldsDataTableConfig()
    {
        // Get all registered controls and build an array that uses the control types as key and value for each entry.
        $controls   = ControlLibrary::instance()->listControls();
        $fieldTypes = array_merge(array_keys($controls['Standard']), array_keys($controls['Widgets']));
        $options    = array_combine($fieldTypes, $fieldTypes);

        return [
            'toolbar' => false,
            'columns' => [
                'add'    => [
                    'title' => 'winter.builder::lang.common.add',
                    'type'  => 'checkbox',
                    'width' => '50px',
                ],
                'column' => [
                    'title'    => 'winter.builder::lang.database.column_name_name',
                    'readOnly' => true,
                ],
                'label'  => [
                    'title' => 'winter.builder::lang.list.column_name_label',
                ],
                'type'   => [
                    'title'   => 'winter.builder::lang.form.control_widget_type',
                    'type'    => 'dropdown',
                    'options' => $options,
                ],
            ],
        ];
    }

    /**
     * Returns the initial value for the DataTable widget that
     * is used in the "add database columns" popup.
     *
     * @param array $columns
     *
     * @return array
     */
    protected function getAddDatabaseFieldsDataTableValue(array $columns)
    {
        // Map database column types to widget types.
        $typeMap = [
            'string'       => 'text',
            'integer'      => 'number',
            'text'         => 'textarea',
            'timestamp'    => 'datepicker',
            'smallInteger' => 'number',
            'bigInteger'   => 'number',
            'date'         => 'datepicker',
            'time'         => 'datepicker',
            'dateTime'     => 'datepicker',
            'binary'       => 'checkbox',
            'boolean'      => 'checkbox',
            'decimal'      => 'number',
            'double'       => 'number',
        ];

        return array_map(function ($column) use ($typeMap) {
            return [
                'column' => $column['name'],
                'label'  => str_replace('_', ' ', ucfirst($column['name'])),
                'type'   => $typeMap[$column['type']] ?? $column['type'],
                'add'    => false,
            ];
        }, $columns);
    }

    /**
     * Conducts a 3-way diff to update a model's $jsonable property.
     *
     * This determines changes made to the fields config within Builder and applies them to the model's
     * $jsonable property, whilst keeping any manual changes made to this property.
     *
     * @param array $modelProp
     * @param array $original
     * @param array $new
     * @return array
     */
    protected function updateJsonable(array $model, array $original, array $new)
    {
        // Determine changes
        $toAdd = array_diff($new, $original);
        $toRemove = array_diff($original, $new);
        $unchanged = array_intersect($original, $new);

        // Add new columns
        foreach ($toAdd as $column) {
            if (!in_array($column, $model)) {
                $model[] = $column;
            }
        }

        // Keep unchanged columns
        foreach ($unchanged as $column) {
            if (!in_array($column, $model)) {
                $model[] = $column;
            }
        }

        // Remove unneeded columns
        foreach ($toRemove as $column) {
            $key = array_search($column, $model);

            if ($key !== false) {
                array_splice($model, $key, 1);
            }
        }

        return $model;
    }
}
