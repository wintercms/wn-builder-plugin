<?php namespace Winter\Builder\Classes;

use Cache;
use Input;
use Exception;
use Winter\Storm\Support\Traits\Singleton;
use ApplicationException;
use Lang;

/**
 * Provides helper methods for Builder CMS components.
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ComponentHelper
{
    use Singleton;

    protected $modelListCache = null;

    public function listGlobalModels()
    {
        if ($this->modelListCache !== null) {
            return $this->modelListCache;
        }

        $key = 'builder-global-model-list';
        $cached = Cache::get($key, false);

        if ($cached !== false && ($cached = @unserialize($cached)) !== false) {
            return $this->modelListCache = $cached;
        }

        $plugins = PluginBaseModel::listAllPluginCodes();

        $result = [];
        foreach ($plugins as $pluginCode) {
            try {
                $pluginCodeObj = new PluginCode($pluginCode);

                $models = ModelModel::listPluginModels($pluginCodeObj);

                $pluginCodeStr = $pluginCodeObj->toCode();
                $pluginModelsNamespace = $pluginCodeObj->toPluginNamespace().'\\Models\\';
                foreach ($models as $model) {
                    $fullClassName = $pluginModelsNamespace.$model->className;

                    $result[$fullClassName] = $pluginCodeStr.' - '.$model->className;
                }
            }
            catch (Exception $ex) {
                // Ignore invalid plugins and models
            }
        }

        $expiresAt = now()->addMinutes(1);
        Cache::put($key, serialize($result), $expiresAt);

        return $this->modelListCache = $result;
    }

    public function getModelClassDesignTime()
    {
        $modelClass = trim(Input::get('modelClass'));

        if ($modelClass && !is_scalar($modelClass)) {
            throw new ApplicationException(Lang::get('winter.builder::lang.components.error_model_class_name_should_be_a_string'));
        }

        if (!strlen($modelClass)) {
            $models = $this->listGlobalModels();
            $modelClass = key($models);
        }

        if (!ModelModel::validateModelClassName($modelClass)) {
            throw new ApplicationException(Lang::get('winter.builder::lang.components.error_invalid_model_class_name'));
        }

        return $modelClass;
    }

    public function listModelColumnNames()
    {
        $modelClass = $this->getModelClassDesignTime();

        $key = md5('builder-global-model-list-'.$modelClass);
        $cached = Cache::get($key, false);

        if ($cached !== false && ($cached = @unserialize($cached)) !== false) {
            return $cached;
        }

        $pluginCodeObj = PluginCode::createFromNamespace($modelClass);

        $modelClassParts = explode('\\', $modelClass); // The full class name is already validated in PluginCode::createFromNamespace()
        $modelClass = array_pop($modelClassParts);

        $columnNames = ModelModel::getModelFields($pluginCodeObj, $modelClass);

        $result = [];
        foreach ($columnNames as $columnName) {
            $result[$columnName] = $columnName;
        }

        $expiresAt = now()->addMinutes(1);
        Cache::put($key, serialize($result), $expiresAt);

        return $result;
    }
}
