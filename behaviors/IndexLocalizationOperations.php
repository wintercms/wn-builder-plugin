<?php namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\LocalizationModel;
use Winter\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin localization management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexLocalizationOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/localizationmodel/fields.yaml';

    public function onLanguageCreateOrOpen()
    {
        $language = Input::get('original_language');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($language, $options);
        $this->vars['originalLanguage'] = $language;

        if ($widget->model->isNewModel()) {
            $widget->model->initContent();
        }

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-globe',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $language),
            'isNewRecord' => $widget->model->isNewModel(),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'language' => $language,
                'defaultLanguage' => LocalizationModel::getDefaultLanguage()
            ])
        ];

        return $result;
    }

    public function onLanguageSave()
    {
        $model = $this->loadOrCreateLocalizationFromPost();
        $model->fill($_POST);
        $model->save(false);

        Flash::success(Lang::get('winter.builder::lang.localization.saved'));
        $result = $this->controller->widget->languageList->updateList();

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->language),
            'tabTitle' => $this->getTabName($model),
            'language' => $model->language
        ];

        if ($model->language === LocalizationModel::getDefaultLanguage()) {
            $pluginCode = $model->getPluginCodeObj()->toCode();

            $registryData = [
                'strings' => LocalizationModel::getPluginRegistryData($pluginCode, null),
                'sections' => LocalizationModel::getPluginRegistryData($pluginCode, 'sections'),
                'pluginCode' => $pluginCode
            ];

            $result['builderResponseData']['registryData'] = $registryData;
        }

        return $result;
    }

    public function onLanguageDelete()
    {
        $model = $this->loadOrCreateLocalizationFromPost();

        $model->deleteModel();

        return $this->controller->widget->languageList->updateList();
    }

    public function onLanguageShowCopyStringsPopup()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $language = trim(Input::get('original_language'));

        $languages = LocalizationModel::listPluginLanguages($pluginCodeObj);

        if (strlen($language)) {
            $languages = array_diff($languages, [$language]);
        }

        return $this->makePartial('copy-strings-popup-form', ['languages'=>$languages]);
    }

    public function onLanguageCopyStringsFrom()
    {
        $sourceLanguage = Request::input('copy_from');
        $destinationText = Request::input('strings');

        $model = new LocalizationModel();
        $model->setPluginCode(Request::input('plugin_code'));

        $responseData = $model->copyStringsFrom($destinationText, $sourceLanguage);

        return ['builderResponseData' => $responseData];
    }

    public function onLanguageLoadAddStringForm()
    {
        return [
            'markup' => $this->makePartial('new-string-popup')
        ];
    }

    public function onLanguageCreateString()
    {
        $stringKey = trim(Request::input('key'));
        $stringValue = trim(Request::input('value'));

        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $pluginCode = $pluginCodeObj->toCode();
        $options = [
            'pluginCode' => $pluginCode
        ];

        $defaultLanguage = LocalizationModel::getDefaultLanguage();
        if (LocalizationModel::languageFileExists($pluginCode, $defaultLanguage)) {
            $model = $this->loadOrCreateBaseModel($defaultLanguage, $options);
        }
        else {
            $model = LocalizationModel::initModel($pluginCode, $defaultLanguage);
        }

        $newStringKey = $model->createStringAndSave($stringKey, $stringValue);
        $pluginCode = $pluginCodeObj->toCode();

        return [
            'localizationData' => [
                'key' => $newStringKey,
                'value' => $stringValue
            ],
            'registryData' => [
                'strings' => LocalizationModel::getPluginRegistryData($pluginCode, null),
                'sections' => LocalizationModel::getPluginRegistryData($pluginCode, 'sections')
            ]
        ];
    }

    public function onLanguageGetStrings()
    {
        $model = $this->loadOrCreateLocalizationFromPost();

        return ['builderResponseData' => [
            'strings' => $model ? $model->strings : null
        ]];
    }

    protected function loadOrCreateLocalizationFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $originalLanguage = Input::get('original_language');

        return $this->loadOrCreateBaseModel($originalLanguage, $options);
    }

    protected function getTabName($model)
    {
        $pluginName = Lang::get($model->getModelPluginName());

        if (!strlen($model->language)) {
            return $pluginName.'/'.Lang::get('winter.builder::lang.localization.tab_new_language');
        }

        return $pluginName.'/'.$model->language;
    }

    protected function getTabId($pluginCode, $language)
    {
        if (!strlen($language)) {
            return 'localization-'.$pluginCode.'-'.uniqid(time());
        }

        return 'localization-'.$pluginCode.'-'.$language;
    }

    protected function loadOrCreateBaseModel($language, $options = [])
    {
        $model = new LocalizationModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$language) {
            return $model;
        }

        $model->load($language);
        return $model;
    }

    /**
     * Reconstructs a localization FormWidget from POST data
     *
     * This allows widgets within localization tabs (like CodeEditor) to make AJAX requests
     * by rebuilding the FormWidget state from the request data
     *
     * @param string $alias The exact widget alias to recreate
     * @return \Backend\Widgets\Form|null The reconstructed form widget
     */
    protected function reconstructFormWidget($alias)
    {
        // Only handle localization tab forms (pattern: form_{md5}{uniqid})
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

        // Get language from request
        $language = Input::get('original_language');

        // Build LocalizationModel from POST data
        $options = ['pluginCode' => $pluginCode];
        $model = $this->loadOrCreateBaseModel($language, $options);

        // Fill with current form data
        $model->fill([
            'language' => Request::input('language', ''),
            'strings' => Request::input('strings', '')
        ]);

        // Create FormWidget with exact same config as original tab
        // Using the EXACT alias from the AJAX handler is critical
        $widgetConfig = $this->makeConfig($this->baseFormConfigFile);
        $widgetConfig->model = $model;
        $widgetConfig->alias = $alias;

        $form = $this->makeWidget('Backend\\Widgets\\Form', $widgetConfig);
        $form->context = strlen($language) ? 'update' : 'create';

        // CRITICAL: Bind to controller so it's available in $this->widget
        // This makes the widget discoverable when the AJAX handler looks it up
        $form->bindToController();

        return $form;
    }
}
