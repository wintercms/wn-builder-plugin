<?php namespace Winter\Builder\Behaviors;

use Lang;
use Flash;
use Request;
use ApplicationException;
use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\ModelModel;

/**
 * Model management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexModelOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/modelmodel/fields.yaml';

    public function onModelLoadPopup()
    {
        $pluginCodeObj = $this->getPluginCode();

        try {
            $widget = $this->makeBaseFormWidget(null);
            $this->vars['form'] = $widget;
            $widget->model->setPluginCodeObj($pluginCodeObj);
            $this->vars['pluginCode'] = $pluginCodeObj->toCode();
        }
        catch (ApplicationException $ex) {
            $this->vars['errorMessage'] = $ex->getMessage();
        }

        return $this->makePartial('model-popup-form');
    }

    public function onModelSave()
    {
        $pluginCode = Request::input('plugin_code');

        $model = $this->loadOrCreateBaseModel(null);
        $model->setPluginCode($pluginCode);

        $model->fill($_POST);
        $model->save();

        $result = $this->controller->widget->modelList->updateList();

        $builderResponseData = [
            'registryData' => [
                'models' => ModelModel::getPluginRegistryData($pluginCode, null),
                'pluginCode' => $pluginCode
            ]
        ];

        $result['builderResponseData'] = $builderResponseData;

        return $result;
    }

    public function onModelDelete()
    {
        $pluginCode = $this->getPluginCode();
        $model = Request::input('model');

        $modelClass = new ModelModel();
        $modelClass->setPluginCode($pluginCode->toCode());
        $modelClass->className = $model;
        $modelClass->deleteModel();

        $result = $this->controller->widget->modelList->updateList();

        Flash::success(Lang::get('winter.builder::lang.model.deleted', ['model' => $model]));

        return $result;
    }

    protected function loadOrCreateBaseModel($className, $options = [])
    {
        // Editing model is not supported, always return
        // a new object.

        return new ModelModel();
    }
}
