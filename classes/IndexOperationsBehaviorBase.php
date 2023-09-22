<?php namespace Winter\Builder\Classes;

use Backend\Classes\ControllerBehavior;
use Backend\Behaviors\FormController;
use ApplicationException;
use Lang;

/**
 * Base class for index operation behaviors
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class IndexOperationsBehaviorBase extends ControllerBehavior
{
    protected $baseFormConfigFile = null;

    protected function makeBaseFormWidget($modelCode, $options = [], $aliasSuffix = null)
    {
        if (!strlen($this->baseFormConfigFile)) {
            throw new ApplicationException(Lang::get('winter.builder::lang.behavior.error_base_form_configuration_file_is_not_specified', ['class' => get_class($this)]));
        }

        $widgetConfig = $this->makeConfig($this->baseFormConfigFile);

        $widgetConfig->model = $this->loadOrCreateBaseModel($modelCode, $options);
        $widgetConfig->alias = 'form_' . md5(get_class($this)) . ($aliasSuffix ?? uniqid());

        $form = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
        $form->context = strlen($modelCode) ? FormController::CONTEXT_UPDATE : FormController::CONTEXT_CREATE;

        return $form;
    }

    protected function getPluginCode()
    {
        $vector = $this->controller->getBuilderActivePluginVector();

        if (!$vector) {
            throw new ApplicationException(Lang::get('winter.builder::lang.behavior.error_cannot_determine_currently_active_plugin'));
        }

        return $vector->pluginCodeObj;
    }

    abstract protected function loadOrCreateBaseModel($modelCode, $options = []);
}
