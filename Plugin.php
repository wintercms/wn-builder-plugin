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
            'replaces'    => ['RainLab.Builder' => '<= 1.0.27'],
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
                'label' => 'winter.builder::lang.plugin.manage_plugins']
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
                'label'       => 'Builder',
                'icon'        => 'icon-wrench',
                'description' => 'Set your author name and namespace for plugin creation.',
                'class'       => 'Winter\Builder\Models\Settings',
                'permissions' => ['winter.builder.manage_plugins'],
                'order'       => 600
            ]
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
            $combiner->registerBundle('$/winter/builder/assets/js/build.js');
        });
    }
}
