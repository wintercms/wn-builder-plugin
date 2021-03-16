<?php
/**
 * To allow compatibility with plugins that extend the original RainLab.Builder plugin, this will alias those classes to
 * use the new Winter.Builder classes.
 */
$aliases = [
    // Regular aliases
    Winter\Builder\Plugin::class                                    => 'RainLab\Builder\Plugin',
    Winter\Builder\Behaviors\IndexDataRegistry::class               => 'RainLab\Builder\Behaviors\IndexDataRegistry',
    Winter\Builder\Behaviors\IndexDatabaseTableOperations::class    => 'RainLab\Builder\Behaviors\IndexDatabaseTableOperations',
    Winter\Builder\Behaviors\IndexModelFormOperations::class        => 'RainLab\Builder\Behaviors\IndexModelFormOperations',
    Winter\Builder\Behaviors\IndexLocalizationOperations::class     => 'RainLab\Builder\Behaviors\IndexLocalizationOperations',
    Winter\Builder\Behaviors\IndexModelListOperations::class        => 'RainLab\Builder\Behaviors\IndexModelListOperations',
    Winter\Builder\Behaviors\IndexVersionsOperations::class         => 'RainLab\Builder\Behaviors\IndexVersionsOperations',
    Winter\Builder\Behaviors\IndexControllerOperations::class       => 'RainLab\Builder\Behaviors\IndexControllerOperations',
    Winter\Builder\Behaviors\IndexMenusOperations::class            => 'RainLab\Builder\Behaviors\IndexMenusOperations',
    Winter\Builder\Behaviors\IndexPermissionsOperations::class      => 'RainLab\Builder\Behaviors\IndexPermissionsOperations',
    Winter\Builder\Behaviors\IndexModelOperations::class            => 'RainLab\Builder\Behaviors\IndexModelOperations',
    Winter\Builder\Behaviors\IndexPluginOperations::class           => 'RainLab\Builder\Behaviors\IndexPluginOperations',
    Winter\Builder\Classes\DatabaseTableModel::class                => 'RainLab\Builder\Classes\DatabaseTableModel',
    Winter\Builder\Classes\PhpSourceStream::class                   => 'RainLab\Builder\Classes\PhpSourceStream',
    Winter\Builder\Classes\ControllerBehaviorLibrary::class         => 'RainLab\Builder\Classes\ControllerBehaviorLibrary',
    Winter\Builder\Classes\PermissionsModel::class                  => 'RainLab\Builder\Classes\PermissionsModel',
    Winter\Builder\Classes\PluginCode::class                        => 'RainLab\Builder\Classes\PluginCode',
    Winter\Builder\Classes\ControlDesignTimeProviderBase::class     => 'RainLab\Builder\Classes\ControlDesignTimeProviderBase',
    Winter\Builder\Classes\MigrationModel::class                    => 'RainLab\Builder\Classes\MigrationModel',
    Winter\Builder\Classes\ComponentHelper::class                   => 'RainLab\Builder\Classes\ComponentHelper',
    Winter\Builder\Classes\IconList::class                          => 'RainLab\Builder\Classes\IconList',
    Winter\Builder\Classes\TableMigrationCodeGenerator::class       => 'RainLab\Builder\Classes\TableMigrationCodeGenerator',
    Winter\Builder\Classes\instancd::class                          => 'RainLab\Builder\Classes\instancd',
    Winter\Builder\Classes\is::class                                => 'RainLab\Builder\Classes\is',
    Winter\Builder\Classes\MigrationColumnType::class               => 'RainLab\Builder\Classes\MigrationColumnType',
    Winter\Builder\Classes\ModelListModel::class                    => 'RainLab\Builder\Classes\ModelListModel',
    Winter\Builder\Classes\PluginBaseModel::class                   => 'RainLab\Builder\Classes\PluginBaseModel',
    Winter\Builder\Classes\LocalizationModel::class                 => 'RainLab\Builder\Classes\LocalizationModel',
    Winter\Builder\Classes\MenusModel::class                        => 'RainLab\Builder\Classes\MenusModel',
    Winter\Builder\Classes\LanguageMixer::class                     => 'RainLab\Builder\Classes\LanguageMixer',
    Winter\Builder\Classes\ControllerModel::class                   => 'RainLab\Builder\Classes\ControllerModel',
    Winter\Builder\Classes\ModelModel::class                        => 'RainLab\Builder\Classes\ModelModel',
    Winter\Builder\Classes\MigrationFileParser::class               => 'RainLab\Builder\Classes\MigrationFileParser',
    Winter\Builder\Classes\ModelFileParser::class                   => 'RainLab\Builder\Classes\ModelFileParser',
    Winter\Builder\Classes\ModelFormModel::class                    => 'RainLab\Builder\Classes\ModelFormModel',
    Winter\Builder\Classes\FilesystemGenerator::class               => 'RainLab\Builder\Classes\FilesystemGenerator',
    Winter\Builder\Classes\ControlLibrary::class                    => 'RainLab\Builder\Classes\ControlLibrary',
    Winter\Builder\Classes\BehaviorDesignTimeProviderBase::class    => 'RainLab\Builder\Classes\BehaviorDesignTimeProviderBase',
    Winter\Builder\Classes\ControllerFileParser::class              => 'RainLab\Builder\Classes\ControllerFileParser',
    Winter\Builder\Components\RecordList::class                     => 'RainLab\Builder\Components\RecordList',
    Winter\Builder\Components\RecordDetails::class                  => 'RainLab\Builder\Components\RecordDetails',
    Winter\Builder\Controllers\Index::class                         => 'RainLab\Builder\Controllers\Index',
    Winter\Builder\FormWidgets\ControllerBuilder::class             => 'RainLab\Builder\FormWidgets\ControllerBuilder',
    Winter\Builder\FormWidgets\FormBuilder::class                   => 'RainLab\Builder\FormWidgets\FormBuilder',
    Winter\Builder\FormWidgets\MenuEditor::class                    => 'RainLab\Builder\FormWidgets\MenuEditor',
    Winter\Builder\Models\MyMock::class                             => 'RainLab\Builder\Models\MyMock',
    Winter\Builder\Models\Settings::class                           => 'RainLab\Builder\Models\Settings',
    Winter\Builder\Widgets\DatabaseTableList::class                 => 'RainLab\Builder\Widgets\DatabaseTableList',
    Winter\Builder\Widgets\ControllerList::class                    => 'RainLab\Builder\Widgets\ControllerList',
    Winter\Builder\Widgets\LanguageList::class                      => 'RainLab\Builder\Widgets\LanguageList',
    Winter\Builder\Widgets\PluginList::class                        => 'RainLab\Builder\Widgets\PluginList',
    Winter\Builder\Widgets\ModelList::class                         => 'RainLab\Builder\Widgets\ModelList',
    Winter\Builder\Widgets\DefaultControlDesignTimeProvider::class  => 'RainLab\Builder\Widgets\DefaultControlDesignTimeProvider',
    Winter\Builder\Widgets\VersionList::class                       => 'RainLab\Builder\Widgets\VersionList',
    Winter\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class => 'RainLab\Builder\Widgets\DefaultBehaviorDesignTimeProvider',
];

foreach ($aliases as $original => $alias) {
    if (!class_exists($alias)) {
        class_alias($original, $alias);
    }
}
