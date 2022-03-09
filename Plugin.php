<?php namespace Winter\Builder;

use Event;
use Lang;
use Backend;
use System\Classes\PluginBase;
use System\Classes\CombineAssets;
use Winter\Builder\Classes\StandardControlsRegistry;
use Winter\Builder\Classes\StandardBehaviorsRegistry;
use Illuminate\Support\Facades\Validator;
use Winter\Builder\Rules\Reserved;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'winter.builder::lang.plugin.name',
            'description' => 'winter.builder::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-wrench',
            'homepage'    => 'https://github.com/wintercms/wn-builder-plugin',
            'replaces'    => ['RainLab.Builder' => '<= 1.1.0'],
        ];
    }

    public function registerComponents()
    {
        return [
            'Winter\Builder\Components\RecordList'       => 'builderList',
            'Winter\Builder\Components\RecordDetails'    => 'builderDetails'
        ];
    }

    public function registerPermissions()
    {
        return [
            'winter.builder.manage_plugins' => [
                'tab' => 'winter.builder::lang.plugin.name',
                'label' => 'winter.builder::lang.plugin.manage_plugins',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'builder' => [
                'label'       => 'winter.builder::lang.plugin.name',
                'url'         => Backend::url('winter/builder'),
                'icon'        => 'icon-wrench',
                'iconSvg'     => 'plugins/winter/builder/assets/images/builder-icon.svg',
                'permissions' => ['winter.builder.manage_plugins'],
                'order'       => 400,

                'sideMenu' => [
                    'database' => [
                        'label'       => 'winter.builder::lang.database.menu_label',
                        'icon'        => 'icon-hdd-o',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'database'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ],
                    'models' => [
                        'label'       => 'winter.builder::lang.model.menu_label',
                        'icon'        => 'icon-random',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'models'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ],
                    'permissions' => [
                        'label'       => 'winter.builder::lang.permission.menu_label',
                        'icon'        => 'icon-unlock-alt',
                        'url'         => '#',
                        'attributes'  => ['data-no-side-panel'=>'true', 'data-builder-command'=>'permission:cmdOpenPermissions', 'data-menu-item'=>'permissions'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ],
                    'menus' => [
                        'label'       => 'winter.builder::lang.menu.menu_label',
                        'icon'        => 'icon-location-arrow',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-no-side-panel'=>'true', 'data-builder-command'=>'menus:cmdOpenMenus', 'data-menu-item'=>'menus'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ],
                    'controllers' => [
                        'label'       => 'winter.builder::lang.controller.menu_label',
                        'icon'        => 'icon-asterisk',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'controllers'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ],
                    'versions' => [
                        'label'       => 'winter.builder::lang.version.menu_label',
                        'icon'        => 'icon-code-fork',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'version'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ],
                    'localization' => [
                        'label'       => 'winter.builder::lang.localization.menu_label',
                        'icon'        => 'icon-globe',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'localization'],
                        'permissions' => ['winter.builder.manage_plugins']
                    ]
                ]

            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'winter.builder::lang.plugin.name',
                'icon'        => 'icon-wrench',
                'description' => 'winter.builder::lang.settings.menu_desc',
                'class'       => 'Winter\Builder\Models\Settings',
                'permissions' => ['winter.builder.manage_plugins'],
                'order'       => 600
            ]
        ];
    }

    public function registerClassAliases()
    {
        /**
         * To allow compatibility with plugins that extend the original RainLab.Builder plugin,
         * this will alias those classes to use the new Winter.Builder classes.
         */
        return [
            \Winter\Builder\Plugin::class                                    => \RainLab\Builder\Plugin::class,
            \Winter\Builder\Behaviors\IndexDataRegistry::class               => \RainLab\Builder\Behaviors\IndexDataRegistry::class,
            \Winter\Builder\Behaviors\IndexDatabaseTableOperations::class    => \RainLab\Builder\Behaviors\IndexDatabaseTableOperations::class,
            \Winter\Builder\Behaviors\IndexModelFormOperations::class        => \RainLab\Builder\Behaviors\IndexModelFormOperations::class,
            \Winter\Builder\Behaviors\IndexLocalizationOperations::class     => \RainLab\Builder\Behaviors\IndexLocalizationOperations::class,
            \Winter\Builder\Behaviors\IndexModelListOperations::class        => \RainLab\Builder\Behaviors\IndexModelListOperations::class,
            \Winter\Builder\Behaviors\IndexVersionsOperations::class         => \RainLab\Builder\Behaviors\IndexVersionsOperations::class,
            \Winter\Builder\Behaviors\IndexControllerOperations::class       => \RainLab\Builder\Behaviors\IndexControllerOperations::class,
            \Winter\Builder\Behaviors\IndexMenusOperations::class            => \RainLab\Builder\Behaviors\IndexMenusOperations::class,
            \Winter\Builder\Behaviors\IndexPermissionsOperations::class      => \RainLab\Builder\Behaviors\IndexPermissionsOperations::class,
            \Winter\Builder\Behaviors\IndexModelOperations::class            => \RainLab\Builder\Behaviors\IndexModelOperations::class,
            \Winter\Builder\Behaviors\IndexPluginOperations::class           => \RainLab\Builder\Behaviors\IndexPluginOperations::class,
            \Winter\Builder\Classes\DatabaseTableModel::class                => \RainLab\Builder\Classes\DatabaseTableModel::class,
            \Winter\Builder\Classes\PhpSourceStream::class                   => \RainLab\Builder\Classes\PhpSourceStream::class,
            \Winter\Builder\Classes\ControllerBehaviorLibrary::class         => \RainLab\Builder\Classes\ControllerBehaviorLibrary::class,
            \Winter\Builder\Classes\PermissionsModel::class                  => \RainLab\Builder\Classes\PermissionsModel::class,
            \Winter\Builder\Classes\PluginCode::class                        => \RainLab\Builder\Classes\PluginCode::class,
            \Winter\Builder\Classes\ControlDesignTimeProviderBase::class     => \RainLab\Builder\Classes\ControlDesignTimeProviderBase::class,
            \Winter\Builder\Classes\MigrationModel::class                    => \RainLab\Builder\Classes\MigrationModel::class,
            \Winter\Builder\Classes\ComponentHelper::class                   => \RainLab\Builder\Classes\ComponentHelper::class,
            \Winter\Builder\Classes\IconList::class                          => \RainLab\Builder\Classes\IconList::class,
            \Winter\Builder\Classes\TableMigrationCodeGenerator::class       => \RainLab\Builder\Classes\TableMigrationCodeGenerator::class,
            \Winter\Builder\Classes\MigrationColumnType::class               => \RainLab\Builder\Classes\MigrationColumnType::class,
            \Winter\Builder\Classes\ModelListModel::class                    => \RainLab\Builder\Classes\ModelListModel::class,
            \Winter\Builder\Classes\PluginBaseModel::class                   => \RainLab\Builder\Classes\PluginBaseModel::class,
            \Winter\Builder\Classes\LocalizationModel::class                 => \RainLab\Builder\Classes\LocalizationModel::class,
            \Winter\Builder\Classes\MenusModel::class                        => \RainLab\Builder\Classes\MenusModel::class,
            \Winter\Builder\Classes\LanguageMixer::class                     => \RainLab\Builder\Classes\LanguageMixer::class,
            \Winter\Builder\Classes\ControllerModel::class                   => \RainLab\Builder\Classes\ControllerModel::class,
            \Winter\Builder\Classes\ModelModel::class                        => \RainLab\Builder\Classes\ModelModel::class,
            \Winter\Builder\Classes\MigrationFileParser::class               => \RainLab\Builder\Classes\MigrationFileParser::class,
            \Winter\Builder\Classes\ModelFileParser::class                   => \RainLab\Builder\Classes\ModelFileParser::class,
            \Winter\Builder\Classes\ModelFormModel::class                    => \RainLab\Builder\Classes\ModelFormModel::class,
            \Winter\Builder\Classes\FilesystemGenerator::class               => \RainLab\Builder\Classes\FilesystemGenerator::class,
            \Winter\Builder\Classes\ControlLibrary::class                    => \RainLab\Builder\Classes\ControlLibrary::class,
            \Winter\Builder\Classes\BehaviorDesignTimeProviderBase::class    => \RainLab\Builder\Classes\BehaviorDesignTimeProviderBase::class,
            \Winter\Builder\Classes\ControllerFileParser::class              => \RainLab\Builder\Classes\ControllerFileParser::class,
            \Winter\Builder\Components\RecordList::class                     => \RainLab\Builder\Components\RecordList::class,
            \Winter\Builder\Components\RecordDetails::class                  => \RainLab\Builder\Components\RecordDetails::class,
            \Winter\Builder\Controllers\Index::class                         => \RainLab\Builder\Controllers\Index::class,
            \Winter\Builder\FormWidgets\ControllerBuilder::class             => \RainLab\Builder\FormWidgets\ControllerBuilder::class,
            \Winter\Builder\FormWidgets\FormBuilder::class                   => \RainLab\Builder\FormWidgets\FormBuilder::class,
            \Winter\Builder\FormWidgets\MenuEditor::class                    => \RainLab\Builder\FormWidgets\MenuEditor::class,
            \Winter\Builder\Models\Settings::class                           => \RainLab\Builder\Models\Settings::class,
            \Winter\Builder\Widgets\DatabaseTableList::class                 => \RainLab\Builder\Widgets\DatabaseTableList::class,
            \Winter\Builder\Widgets\ControllerList::class                    => \RainLab\Builder\Widgets\ControllerList::class,
            \Winter\Builder\Widgets\LanguageList::class                      => \RainLab\Builder\Widgets\LanguageList::class,
            \Winter\Builder\Widgets\PluginList::class                        => \RainLab\Builder\Widgets\PluginList::class,
            \Winter\Builder\Widgets\ModelList::class                         => \RainLab\Builder\Widgets\ModelList::class,
            \Winter\Builder\Widgets\DefaultControlDesignTimeProvider::class  => \RainLab\Builder\Widgets\DefaultControlDesignTimeProvider::class,
            \Winter\Builder\Widgets\VersionList::class                       => \RainLab\Builder\Widgets\VersionList::class,
            \Winter\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class => \RainLab\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class,
        ];
    }

    public function boot()
    {
        Event::listen('pages.builder.registerControls', function ($controlLibrary) {
            new StandardControlsRegistry($controlLibrary);
        });

        Event::listen('pages.builder.registerControllerBehaviors', function ($behaviorLibrary) {
            new StandardBehaviorsRegistry($behaviorLibrary);
        });

        // Register reserved keyword validation
        Event::listen('translator.beforeResolve', function ($key, $replaces, $locale) {
            if ($key === 'validation.reserved') {
                return Lang::get('winter.builder::lang.validation.reserved');
            }
        });

        Validator::extend('reserved', Reserved::class);
        Validator::replacer('reserved', function ($message, $attribute, $rule, $parameters) {
            // Fixes lowercase attribute names in the new plugin modal form
            return ucfirst($message);
        });
    }

    public function register()
    {
        /*
         * Register asset bundles
         */
        CombineAssets::registerCallback(function ($combiner) {
            $combiner->registerBundle('$/winter/builder/assets/less/builder.less');
            $combiner->registerBundle('$/winter/builder/assets/js/build.js');
        });
    }
}
