<?php namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\MigrationModel;
use Winter\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin version management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexVersionsOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/migrationmodel/management-fields.yaml';

    public function onVersionCreateOrOpen()
    {
        $versionNumber = Input::get('original_version');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($versionNumber, $options);
        $this->vars['originalVersion'] = $versionNumber;

        if ($widget->model->isNewModel()) {
            $versionType = Input::get('version_type');
            $widget->model->initVersion($versionType);
        }

        $result = [
            'tabTitle' => $this->getTabName($versionNumber, $widget->model),
            'tabIcon' => 'icon-code-fork',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $versionNumber),
            'isNewRecord' => $widget->model->isNewModel(),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'originalVersion' => $versionNumber
            ])
        ];

        return $result;
    }

    public function onVersionSave()
    {
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save(false);

        Flash::success(Lang::get('winter.builder::lang.version.saved'));
        $result = $this->controller->widget->versionList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->version),
            'tabTitle' => $this->getTabName($model->version, $model),
            'savedVersion' => $model->version,
            'isApplied' => $model->isApplied()
        ];

        return $result;
    }

    public function onVersionDelete()
    {
        $model = $this->loadOrCreateListFromPost();

        $model->deleteModel();

        return $this->controller->widget->versionList->updateList();
    }

    public function onVersionApply()
    {
        // Save the version before applying it
        //
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save(false);

        // Apply the version
        //
        $model->apply();

        Flash::success(Lang::get('winter.builder::lang.version.applied'));
        $result = $this->controller->widget->versionList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->version),
            'tabTitle' => $this->getTabName($model->version, $model),
            'savedVersion' => $model->version
        ];

        return $result;
    }

    public function onVersionRollback()
    {
        // Save the version before rolling it back
        //
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save(false);

        // Rollback the version
        //
        $model->rollback();

        Flash::success(Lang::get('winter.builder::lang.version.rolled_back'));
        $result = $this->controller->widget->versionList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->version),
            'tabTitle' => $this->getTabName($model->version, $model),
            'savedVersion' => $model->version
        ];

        return $result;
    }

    protected function loadOrCreateListFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $versionNumber = Input::get('original_version');

        return $this->loadOrCreateBaseModel($versionNumber, $options);
    }

    protected function getTabName($version, $model)
    {
        $pluginName = Lang::get($model->getModelPluginName());

        if (!strlen($version)) {
            return $pluginName.'/'.Lang::get('winter.builder::lang.version.tab_new_version');
        }

        return $pluginName.'/v'.$version;
    }

    protected function getTabId($pluginCode, $version)
    {
        if (!strlen($version)) {
            return 'version-'.$pluginCode.'-'.uniqid(time());
        }

        return 'version-'.$pluginCode.'-'.$version;
    }

    protected function loadOrCreateBaseModel($versionNumber, $options = [])
    {
        $model = new MigrationModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$versionNumber) {
            return $model;
        }

        $model->load($versionNumber);
        return $model;
    }

    /**
     * Reconstructs a version FormWidget from POST data
     *
     * This allows widgets within version tabs (like CodeEditor) to make AJAX requests
     * by rebuilding the FormWidget state from the request data
     *
     * @param string $alias The exact widget alias to recreate
     * @return \Backend\Widgets\Form|null The reconstructed form widget
     */
    protected function reconstructFormWidget($alias)
    {
        // Only handle version tab forms (pattern: form_{md5}{uniqid})
        // Not migration popup forms (pattern: form_migration_{uniqid}_)
        if (preg_match('/^form_migration_[a-z0-9]+_$/i', $alias)) {
            return parent::reconstructFormWidget($alias);
        }

        // Check if this alias belongs to this behavior
        $expectedPrefix = 'form_' . md5(get_class($this));
        if (strpos($alias, $expectedPrefix) !== 0) {
            return parent::reconstructFormWidget($alias);
        }

        // Get plugin code from request or fall back to active plugin
        $pluginCode = Request::input('plugin_code');
        if (!$pluginCode) {
            $pluginCode = $this->getPluginCode()->toCode();
        }

        // Get version number from request
        $versionNumber = Input::get('original_version');

        // Build MigrationModel from POST data
        $options = ['pluginCode' => $pluginCode];
        $model = $this->loadOrCreateBaseModel($versionNumber, $options);

        // Fill with current form data
        $model->fill([
            'version' => Request::input('version', ''),
            'description' => Request::input('description', ''),
            'code' => Request::input('code', ''),
            'scriptFileName' => Request::input('scriptFileName', '')
        ]);

        // Create FormWidget with exact same config as original tab
        // Using the EXACT alias from the AJAX handler is critical
        $widgetConfig = $this->makeConfig($this->baseFormConfigFile);
        $widgetConfig->model = $model;
        $widgetConfig->alias = $alias;

        $form = $this->makeWidget('Backend\\Widgets\\Form', $widgetConfig);
        $form->context = strlen($versionNumber) ? 'update' : 'create';

        // CRITICAL: Bind to controller so it's available in $this->widget
        // This makes the widget discoverable when the AJAX handler looks it up
        $form->bindToController();

        return $form;
    }
}
