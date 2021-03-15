<?php

if (!class_exists(RainLab\Builder\Plugin::class)) {
    class_alias(Winter\Builder\Plugin::class, RainLab\Builder\Plugin::class);

    class_alias(Winter\Builder\Behaviors\IndexDataRegistry::class, RainLab\Builder\Behaviors\IndexDataRegistry::class);
    class_alias(Winter\Builder\Behaviors\IndexDatabaseTableOperations::class, RainLab\Builder\Behaviors\IndexDatabaseTableOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexModelFormOperations::class, RainLab\Builder\Behaviors\IndexModelFormOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexLocalizationOperations::class, RainLab\Builder\Behaviors\IndexLocalizationOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexModelListOperations::class, RainLab\Builder\Behaviors\IndexModelListOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexVersionsOperations::class, RainLab\Builder\Behaviors\IndexVersionsOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexControllerOperations::class, RainLab\Builder\Behaviors\IndexControllerOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexMenusOperations::class, RainLab\Builder\Behaviors\IndexMenusOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexPermissionsOperations::class, RainLab\Builder\Behaviors\IndexPermissionsOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexModelOperations::class, RainLab\Builder\Behaviors\IndexModelOperations::class);
    class_alias(Winter\Builder\Behaviors\IndexPluginOperations::class, RainLab\Builder\Behaviors\IndexPluginOperations::class);

    class_alias(Winter\Builder\Classes\DatabaseTableModel::class, RainLab\Builder\Classes\DatabaseTableModel::class);
    class_alias(Winter\Builder\Classes\PhpSourceStream::class, RainLab\Builder\Classes\PhpSourceStream::class);
    class_alias(Winter\Builder\Classes\ControllerBehaviorLibrary::class, RainLab\Builder\Classes\ControllerBehaviorLibrary::class);
    class_alias(Winter\Builder\Classes\PermissionsModel::class, RainLab\Builder\Classes\PermissionsModel::class);
    class_alias(Winter\Builder\Classes\PluginCode::class, RainLab\Builder\Classes\PluginCode::class);
    class_alias(Winter\Builder\Classes\ControlDesignTimeProviderBase::class, RainLab\Builder\Classes\ControlDesignTimeProviderBase::class);
    class_alias(Winter\Builder\Classes\MigrationModel::class, RainLab\Builder\Classes\MigrationModel::class);
    class_alias(Winter\Builder\Classes\ComponentHelper::class, RainLab\Builder\Classes\ComponentHelper::class);
    class_alias(Winter\Builder\Classes\IconList::class, RainLab\Builder\Classes\IconList::class);
    class_alias(Winter\Builder\Classes\TableMigrationCodeGenerator::class, RainLab\Builder\Classes\TableMigrationCodeGenerator::class);
    class_alias(Winter\Builder\Classes\instancd::class, RainLab\Builder\Classes\instancd::class);
    class_alias(Winter\Builder\Classes\is::class, RainLab\Builder\Classes\is::class);
    class_alias(Winter\Builder\Classes\MigrationColumnType::class, RainLab\Builder\Classes\MigrationColumnType::class);
    class_alias(Winter\Builder\Classes\ModelListModel::class, RainLab\Builder\Classes\ModelListModel::class);
    class_alias(Winter\Builder\Classes\PluginBaseModel::class, RainLab\Builder\Classes\PluginBaseModel::class);
    class_alias(Winter\Builder\Classes\LocalizationModel::class, RainLab\Builder\Classes\LocalizationModel::class);
    class_alias(Winter\Builder\Classes\MenusModel::class, RainLab\Builder\Classes\MenusModel::class);
    class_alias(Winter\Builder\Classes\LanguageMixer::class, RainLab\Builder\Classes\LanguageMixer::class);
    class_alias(Winter\Builder\Classes\ControllerModel::class, RainLab\Builder\Classes\ControllerModel::class);
    class_alias(Winter\Builder\Classes\ModelModel::class, RainLab\Builder\Classes\ModelModel::class);
    class_alias(Winter\Builder\Classes\MigrationFileParser::class, RainLab\Builder\Classes\MigrationFileParser::class);
    class_alias(Winter\Builder\Classes\ModelFileParser::class, RainLab\Builder\Classes\ModelFileParser::class);
    class_alias(Winter\Builder\Classes\ModelFormModel::class, RainLab\Builder\Classes\ModelFormModel::class);
    class_alias(Winter\Builder\Classes\FilesystemGenerator::class, RainLab\Builder\Classes\FilesystemGenerator::class);
    class_alias(Winter\Builder\Classes\ControlLibrary::class, RainLab\Builder\Classes\ControlLibrary::class);
    class_alias(Winter\Builder\Classes\BehaviorDesignTimeProviderBase::class, RainLab\Builder\Classes\BehaviorDesignTimeProviderBase::class);
    class_alias(Winter\Builder\Classes\ControllerFileParser::class, RainLab\Builder\Classes\ControllerFileParser::class);

    class_alias(Winter\Builder\Components\RecordList::class, RainLab\Builder\Components\RecordList::class);
    class_alias(Winter\Builder\Components\RecordDetails::class, RainLab\Builder\Components\RecordDetails::class);

    class_alias(Winter\Builder\Controllers\Index::class, RainLab\Builder\Controllers\Index::class);

    class_alias(Winter\Builder\FormWidgets\ControllerBuilder::class, RainLab\Builder\FormWidgets\ControllerBuilder::class);
    class_alias(Winter\Builder\FormWidgets\FormBuilder::class, RainLab\Builder\FormWidgets\FormBuilder::class);
    class_alias(Winter\Builder\FormWidgets\MenuEditor::class, RainLab\Builder\FormWidgets\MenuEditor::class);

    class_alias(Winter\Builder\Models\MyMock::class, RainLab\Builder\Models\MyMock::class);
    class_alias(Winter\Builder\Models\Settings::class, RainLab\Builder\Models\Settings::class);

    class_alias(Winter\Builder\Widgets\DatabaseTableList::class, RainLab\Builder\Widgets\DatabaseTableList::class);
    class_alias(Winter\Builder\Widgets\ControllerList::class, RainLab\Builder\Widgets\ControllerList::class);
    class_alias(Winter\Builder\Widgets\LanguageList::class, RainLab\Builder\Widgets\LanguageList::class);
    class_alias(Winter\Builder\Widgets\PluginList::class, RainLab\Builder\Widgets\PluginList::class);
    class_alias(Winter\Builder\Widgets\ModelList::class, RainLab\Builder\Widgets\ModelList::class);
    class_alias(Winter\Builder\Widgets\DefaultControlDesignTimeProvider::class, RainLab\Builder\Widgets\DefaultControlDesignTimeProvider::class);
    class_alias(Winter\Builder\Widgets\VersionList::class, RainLab\Builder\Widgets\VersionList::class);
    class_alias(Winter\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class, RainLab\Builder\Widgets\DefaultBehaviorDesignTimeProvider::class);
}
