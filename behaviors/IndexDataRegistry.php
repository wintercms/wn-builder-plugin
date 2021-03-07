<?php namespace Winter\Builder\Behaviors;

use Backend\Classes\ControllerBehavior;
use Winter\Builder\Classes\PluginCode;
use Winter\Builder\Classes\LocalizationModel;
use Winter\Builder\Classes\ModelModel;
use Winter\Builder\Classes\ModelFormModel;
use Winter\Builder\Classes\ModelListModel;
use Winter\Builder\Classes\ControllerModel;
use Winter\Builder\Classes\PermissionsModel;
use ApplicationException;
use SystemException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin data registry functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexDataRegistry extends ControllerBehavior
{
    public function onPluginDataRegistryGetData()
    {
        $code = Input::get('registry_plugin_code');
        $type = Input::get('registry_data_type');
        $subtype = Input::get('registry_data_subtype');

        $result = null;

        switch ($type) {
            case 'localization':
                $result = LocalizationModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-classes':
                $result = ModelModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-forms':
                $result = ModelFormModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-lists':
                $result = ModelListModel::getPluginRegistryData($code, $subtype);
                break;
            case 'controller-urls':
                $result = ControllerModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-columns':
                $result = ModelModel::getPluginRegistryDataColumns($code, $subtype);
                break;
            case 'plugin-lists':
                $result = ModelListModel::getPluginRegistryDataAllRecords($code);
                break;
            case 'permissions':
                $result = PermissionsModel::getPluginRegistryData($code);
                break;
            default:
                throw new SystemException('Unknown plugin registry data type requested.');
        }

        return ['registryData' => $result];
    }
}
