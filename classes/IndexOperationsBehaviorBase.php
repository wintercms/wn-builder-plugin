<?php namespace Winter\Builder\Classes;

use Backend\Classes\ControllerBehavior;
use Backend\Behaviors\FormController;
use ApplicationException;
use Request;
use Exception;

/**
 * Base class for index operation behaviors
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class IndexOperationsBehaviorBase extends ControllerBehavior
{
    protected $baseFormConfigFile = null;

    /**
     * Constructor - Set up AJAX event listener for FormWidget reconstruction
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        // Reconstruct FormWidgets for AJAX requests from CodeEditor and other widgets
        // This allows widgets within forms to make AJAX calls by rebuilding FormWidget state
        $controller->bindEvent('ajax.beforeRunHandler', function ($handler) {
            return $this->handleFormWidgetAjax($handler);
        });
    }

    protected function makeBaseFormWidget($modelCode, $options = [], $aliasSuffix = null)
    {
        if (!strlen($this->baseFormConfigFile)) {
            throw new ApplicationException(sprintf('Base form configuration file is not specified for %s behavior', get_class($this)));
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
            throw new ApplicationException('Cannot determine the currently active plugin.');
        }

        return $vector->pluginCodeObj;
    }

    /**
     * Handle AJAX requests from form widgets (like CodeEditor)
     * Reconstructs the FormWidget if it doesn't exist
     *
     * @param string $handler The AJAX handler name (e.g., "form_xxx::onLoadTheme")
     * @return mixed Null to continue, or response to override
     */
    protected function handleFormWidgetAjax($handler)
    {
        // Only process widget-scoped handlers (format: alias::method)
        if (!strpos($handler, '::')) {
            return null;
        }

        list($widgetAlias, $handlerName) = explode('::', $handler, 2);

        // Extract the form widget alias from nested widget aliases
        // Nested widgets have pattern: {formAlias}{FieldName}
        // Example: form_71c55b7edc094f5b8236ee9af75fe35d695591ef6aa4eCode
        //   where form_71c55b7edc094f5b8236ee9af75fe35d695591ef6aa4e is form alias, Code is field name

        // Match any form widget alias pattern used by Builder behaviors:
        // 1. form_migration_{uniqid}_ (used by popups in IndexDatabaseTableOperations)
        // 2. form_{md5hash}{uniqid} (used by base class for tabs)
        if (!preg_match('/^(form_[a-z0-9_]+)[A-Z]/', $widgetAlias, $matches)) {
            return null;
        }

        $formAlias = $matches[1];

        // Skip if form widget already exists (normal flow worked)
        if (isset($this->controller->widget->{$formAlias})) {
            return null;
        }

        // Reconstruct the FormWidget from request data
        try {
            $this->reconstructFormWidget($formAlias);

            if (config('app.debug')) {
                trace_log("Reconstructed FormWidget for nested widget AJAX", [
                    'behavior' => get_class($this),
                    'nested_widget_alias' => $widgetAlias,
                    'form_alias' => $formAlias,
                    'handler' => Request::header('X_WINTER_REQUEST_HANDLER'),
                    'method' => $handlerName,
                ]);
            }
        } catch (Exception $ex) {
            // Log the error but don't break - let normal flow handle the missing widget error
            trace_log("Failed to reconstruct form widget: " . $ex->getMessage());
        }

        // Return null to continue with normal handler execution
        return null;
    }

    /**
     * Reconstructs a FormWidget from POST data
     * Child classes should override this to provide behavior-specific reconstruction
     *
     * @param string $alias The exact widget alias to recreate
     * @return \Backend\Widgets\Form|null The reconstructed form widget, or null if not supported
     */
    protected function reconstructFormWidget($alias)
    {
        // Default implementation - child classes can override
        return null;
    }

    abstract protected function loadOrCreateBaseModel($modelCode, $options = []);
}
