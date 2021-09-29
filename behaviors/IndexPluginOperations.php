<?php namespace Winter\Builder\Behaviors;

use Request;
use ApplicationException;
use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\PluginBaseModel;

/**
 * Plugin management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 * @author Winter CMS
 */
class IndexPluginOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/pluginbasemodel/fields.yaml';

    /**
     * Form instance
     *
     * @var Form
     */
    protected $formInstance;

    public function __construct($controller)
    {
        parent::__construct($controller);

        if (Request::ajax()) {
            $this->formInstance = $this->makeBaseFormWidget(post('pluginCode'), [], 'pluginPopup');
            $this->formInstance->bindToController();
        }
    }

    public function onPluginLoadPopup()
    {
        $pluginCode = post('pluginCode');

        try {
            $this->vars['form'] = $this->formInstance;
            $this->vars['pluginCode'] = $pluginCode;
        }
        catch (ApplicationException $ex) {
            $this->vars['errorMessage'] = $ex->getMessage();
        }

        return $this->makePartial('plugin-popup-form');
    }

    public function onPluginSave()
    {
        $pluginCode = post('pluginCode');

        $model = $this->loadOrCreateBaseModel($pluginCode);
        $model->fill(array_replace([
            'replaces' => [],
        ], post()));
        $model->save();

        if (!$pluginCode) {
            $result = [];

            $result['responseData'] = [
                'pluginCode' => $model->getPluginCode(),
                'isNewPlugin' => 1
            ];

            return $result;
        } else {
            $result = [];

            $result['responseData'] = [
                'pluginCode' => $model->getPluginCode()
            ];

            return array_merge($result, $this->controller->updatePluginList());
        }
    }

    public function onPluginSetActive()
    {
        $pluginCode = post('pluginCode');
        $updatePluginList = post('updatePluginList');

        $result = $this->controller->setBuilderActivePlugin($pluginCode, false);

        if ($updatePluginList) {
            $result = array_merge($result, $this->controller->updatePluginList());
        }

        $result['responseData'] = ['pluginCode'=>$pluginCode];

        return $result;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new PluginBaseModel();

        if (!$pluginCode) {
            $model->initDefaults();
            return $model;
        }

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
