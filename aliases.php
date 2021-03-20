<?php

use Winter\Storm\Support\ClassLoader;

/**
 * To allow compatibility with plugins that extend the original RainLab.Builder plugin, this will alias those classes to
 * use the new Winter.Builder classes.
 */
$aliases = [
    // Regular aliases
    Winter\Builder\Plugin::class                                    => RainLab\Builder\Plugin::class,
    Winter\Builder\Behaviors\IndexDataRegistry::class               => RainLab\Builder\Behaviors\IndexDataRegistry::class,
    Winter\Builder\Behaviors\IndexDatabaseTableOperations::class    => RainLab\Builder\Behaviors\IndexDatabaseTableOperations::class,
    Winter\Builder\Behaviors\IndexModelFormOperations::class        => RainLab\Builder\Behaviors\IndexModelFormOperations::class,
    Winter\Builder\Behaviors\IndexLocalizationOperations::class     => RainLab\Builder\Behaviors\IndexLocalizationOperations::class,
    Winter\Builder\Behaviors\IndexModelListOperations::class        => RainLab\Builder\Behaviors\IndexModelListOperations::class,
    Winter\Builder\Behaviors\IndexVersionsOperations::class         => RainLab\Builder\Behaviors\IndexVersionsOperations::class,
    Winter\Builder\Behaviors\IndexControllerOperations::class       => RainLab\Builder\Behaviors\IndexControllerOperations::class,
    Winter\Builder\Behaviors\IndexMenusOperations::class            => RainLab\Builder\Behaviors\IndexMenusOperations::class,
    Winter\Builder\Behaviors\IndexPermissionsOperations::class      => RainLab\Builder\Behaviors\IndexPermissionsOperations::class,
    Winter\Builder\Behaviors\IndexModelOperations::class            => RainLab\Builder\Behaviors\IndexModelOperations::class,
    Winter\Builder\Behaviors\IndexPluginOperations::class           => RainLab\Builder\Behaviors\IndexPluginOperations::class,
    Winter\Builder\Classes\DatabaseTableModel::class                => RainLab\Builder\Classes\DatabaseTableModel::class,
    Winter\Builder\Classes\PhpSourceStream::class                   => RainLab\Builder\Classes\PhpSourceStream::class,
    Winter\Builder\Classes\ControllerBehaviorLibrary::class         => RainLab\Builder\Classes\ControllerBehaviorLibrary::class,
    Winter\Builder\Classes\PermissionsModel::class                  => RainLab\Builder\Classes\PermissionsModel::class,
    Winter\Builder\Classes\PluginCode::class                        => RainLab\Builder\Classes\PluginCode::class,
    Winter\Builder\Classes\ControlDesignTimeProviderBase::class     => RainLab\Builder\Classes\ControlDesignTimeProviderBase::class,
    Winter\Builder\Classes\MigrationModel::class                    => RainLab\Builder\Classes\MigrationModel::class,
    Winter\Builder\Classes\ComponentHelper::class                   => RainLab\Builder\Classes\ComponentHelper::class,
    Winter\Builder\Classes\IconList::class                          => RainLab\Builder\Classes\IconList::class,
    Winter\Builder\Classes\TableMigrationCodeGenerator::class       => RainLab\Builder\Classes\TableMigrationCodeGenerator::class,
    Winter\Builder\Classes\instancd::class                          => RainLab\Builder\Classes\instancd::class,
    Winter\Builder\Classes\is::class                                => RainLab\Builder\Classes\is::class,
    Winter\Builder\Classes\MigrationColumnType::class               => RainLab\Builder\Classes\MigrationColumnType::class,
    Winter\Builder\Classes\ModelListModel::class                    => RainLab\Builder\Classes\ModelListModel::class,
    Winter\Builder\Classes\PluginBaseModel::class                   => RainLab\Builder\Classes\PluginBaseModel::class,
    Winter\Builder\Classes\LocalizationModel::class                 => RainLab\Builder\Classes\LocalizationModel::class,
    Winter\Builder\Classes\MenusModel::class                        => RainLab\Builder\Classes\MenusModel::class,
    Winter\Builder\Classes\LanguageMixer::class                     => RainLab\Builder\Classes\LanguageMixer::class,
    Winter\Builder\Classes\ControllerModel::class                   => RainLab\Builder\Classes\ControllerModel::class,
    Winter\Builder\Classes\ModelModel::class                        => RainLab\Builder\Classes\ModelModel::class,
    Winter\Builder\Classes\MigrationFileParser::class               => RainLab\Builder\Classes\MigrationFileParser::class,
    Winter\Builder\Classes\ModelFileParser::class                   => RainLab\Builder\Classes\ModelFileParser::class,
    Winter\Builder\Classes\ModelFormModel::class                    => RainLab\Builder\Classes\ModelFormModel::class,
    Winter\Builder\Classes\FilesystemGenerator::class               => RainLab\Builder\Classes\FilesystemGenerator::class,
    Winter\Builder\Classes\ControlLibrary::class                    => RainLab\Builder\Classes\ControlLibrary::class,
    Winter\Builder\Classes\BehaviorDesignTimeProviderBase::class    => RainLab\Builder\Classes\BehaviorDesignTimeProviderBase::class,
    Winter\Builder\Classes\ControllerFileParser::class              => RainLab\Builder\Classes\ControllerFileParser::class,
    Winter\Builder\Components\RecordList::class                     => RainLab\Builder\Components\RecordList::class,
    Winter\Builder\Components\RecordDetails::class                  => RainLab\Builder\Components\RecordDetails::class,
    Winter\Builder\Controllers\Index::class                         => RainLab\Builder\Controllers\Index::class,
    Winter\Builder\FormWidgets\ControllerBuilder::class             => RainLab\Builder\FormWidgets\ControllerBuilder::class,
    Winter\Builder\FormWidgets\FormBuilder::class                   => RainLab\Builder\FormWidgets\FormBuilder::class,
    Winter\Builder\FormWidgets\MenuEditor::class                    => RainLab\Builder\FormWidgets\MenuEditor::class,
    Winter\Builder\Models\MyMock::class                             => RainLab\Builder\Models\MyMock::class,
    Winter\Builder\Models\Settings::class                           => RainLab\Builder\Models\Settings::class,
    Winter\Builder\Widgets\DatabaseTableList::class                 => RainLab\Builder\Widgets\DatabaseTableList::class,
    Winter\Builder\Widgets\ControllerList::class                    => RainLab\Builder\Widgets\ControllerList::class,
    Winter\Builder\Widgets\LanguageList::class                      => RainLab\Builder\Widgets\LanguageList::class,
    Winter\Builder\Widgets\PluginList::class                        => RainLab\Builder\Widgets\PluginList::class,
    Winter\Builder\Widgets\ModelList::class                         => RainLab\Builder\Widgets\ModelList::class,
    Winter\Builder\Widgets\DefaultControlDesignTimeProvider::class  => RainLab\Builder\Widgets\DefaultControlDesignTimeProvider::class,
    Winter\Builder\Widgets\VersionList::class                       => RainLab\Builder\Widgets\VersionList::class,
    Winter\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class => RainLab\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class,
];

app(ClassLoader::class)->addAliases($aliases);
