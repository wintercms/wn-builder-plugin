<?php namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\MenusModel;
use Winter\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin back-end menu management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexMenusOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/menusmodel/fields.yaml';

    public function onMenusOpen()
    {
        $pluginCodeObj = $this->getPluginCode();

        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeBaseFormWidget($pluginCode);

        $result = [
            'tabTitle' => $widget->model->getPluginName().'/'.Lang::get('winter.builder::lang.menu.tab'),
            'tabIcon' => 'icon-location-arrow',
            'tabId' => $this->getTabId($pluginCode),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onMenusSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill($_POST);
        $model->save();

        Flash::success(Lang::get('winter.builder::lang.menu.saved'));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.Lang::get('winter.builder::lang.menu.tab'),
        ];

        return $result;
    }

    protected function getTabId($pluginCode)
    {
        return 'menus-'.$pluginCode;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new MenusModel();

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
