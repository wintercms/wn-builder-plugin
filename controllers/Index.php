<?php namespace Winter\Builder\Controllers;

use Backend\Classes\Controller;
use Backend\Traits\InspectableContainer;
use Winter\Builder\Widgets\PluginList;
use Winter\Builder\Widgets\DatabaseTableList;
use Winter\Builder\Widgets\ModelList;
use Winter\Builder\Widgets\VersionList;
use Winter\Builder\Widgets\LanguageList;
use Winter\Builder\Widgets\ControllerList;
use Backend;
use BackendMenu;
use Config;

/**
 * Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class Index extends Controller
{
    use InspectableContainer;

    public $implement = [
        'Winter.Builder.Behaviors.IndexPluginOperations',
        'Winter.Builder.Behaviors.IndexDatabaseTableOperations',
        'Winter.Builder.Behaviors.IndexModelOperations',
        'Winter.Builder.Behaviors.IndexModelFormOperations',
        'Winter.Builder.Behaviors.IndexModelListOperations',
        'Winter.Builder.Behaviors.IndexPermissionsOperations',
        'Winter.Builder.Behaviors.IndexMenusOperations',
        'Winter.Builder.Behaviors.IndexVersionsOperations',
        'Winter.Builder.Behaviors.IndexLocalizationOperations',
        'Winter.Builder.Behaviors.IndexControllerOperations',
        'Winter.Builder.Behaviors.IndexDataRegistry'
    ];

    public $requiredPermissions = ['winter.builder.manage_plugins'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Winter.Builder', 'builder', 'database');

        $this->bodyClass = 'compact-container';
        $this->pageTitle = 'winter.builder::lang.plugin.name';

        new PluginList($this, 'pluginList');
        new DatabaseTableList($this, 'databaseTabelList');
        new ModelList($this, 'modelList');
        new VersionList($this, 'versionList');
        new LanguageList($this, 'languageList');
        new ControllerList($this, 'controllerList');
    }

    public function index()
    {
        $this->addCss('/plugins/winter/builder/assets/css/builder.css', 'Winter.Builder');

        // The table widget scripts should be preloaded
        $this->addJs('/modules/backend/widgets/table/assets/js/build-min.js', 'core');

        if (Config::get('develop.decompileBackendAssets', false)) {
            // Allow decompiled backend assets for Winter Builder
            $assets = Backend::decompileAsset('../../plugins/winter/builder/assets/js/build.js', true);

            foreach ($assets as $asset) {
                $this->addJs($asset, 'Winter.Builder');
            }
        } else {
            $this->addJs('/plugins/winter/builder/assets/js/build-min.js', 'Winter.Builder');
        }

        $this->pageTitleTemplate = '%s Builder';
    }

    public function setBuilderActivePlugin($pluginCode, $refreshPluginList = false)
    {
        $this->widget->pluginList->setActivePlugin($pluginCode);

        $result = [];
        if ($refreshPluginList) {
            $result = $this->widget->pluginList->updateList();
        }

        $result = array_merge(
            $result,
            $this->widget->databaseTabelList->refreshActivePlugin(),
            $this->widget->modelList->refreshActivePlugin(),
            $this->widget->versionList->refreshActivePlugin(),
            $this->widget->languageList->refreshActivePlugin(),
            $this->widget->controllerList->refreshActivePlugin()
        );

        return $result;
    }

    public function getBuilderActivePluginVector()
    {
        return $this->widget->pluginList->getActivePluginVector();
    }

    public function updatePluginList()
    {
        return $this->widget->pluginList->updateList();
    }
}
