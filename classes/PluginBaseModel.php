<?php namespace Winter\Builder\Classes;

use Winter\Builder\Models\Settings as PluginSettings;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use Exception;
use File;

/**
 * Manages plugin basic information.
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginBaseModel extends PluginYamlModel
{
    public $name;

    public $namespace;

    public $description;

    public $author;

    public $icon;

    public $author_namespace;

    public $homepage;

    public $replaces = [];

    protected $localizedName;

    protected $localizedDescription;

    protected $yamlSection = "plugin";

    protected static $fillable = [
        'name',
        'author',
        'namespace',
        'author_namespace',
        'description',
        'icon',
        'homepage',
        'replaces',
    ];

    protected $validationRules = [
        'name' => 'required',
        'author'   => ['required'],
        'namespace'   => ['required', 'regex:/^[a-z]+[a-z0-9]+$/i', 'reserved'],
        'author_namespace' => ['required', 'regex:/^[a-z]+[a-z0-9]+$/i', 'reserved'],
        'homepage' => 'url',
        'replaces' => 'array',
    ];

    public function getIconOptions()
    {
        return IconList::getList();
    }

    public function initDefaults()
    {
        $settings = PluginSettings::instance();
        $this->author = $settings->author_name;
        $this->author_namespace = $settings->author_namespace;
    }

    public function getPluginCode()
    {
        return $this->author_namespace.'.'.$this->namespace;
    }

    public static function listAllPluginCodes()
    {
        $plugins = PluginManager::instance()->getPlugins();

        return array_keys($plugins);
    }

    protected function initPropertiesFromPluginCodeObject($pluginCodeObj)
    {
        $this->author_namespace = $pluginCodeObj->getAuthorCode();
        $this->namespace = $pluginCodeObj->getPluginCode();
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        $array = [];
        $attributes = [
            'name',
            'description',
            'author',
            'icon',
            'homepage',
            'replaces',
        ];

        foreach ($attributes as $attribute) {
            if ($attribute === 'replaces') {
                $array[$attribute] = [];

                foreach ($this->{$attribute} as $replace) {
                    $array[$attribute][$replace['plugin_code']] = $replace['version_constraint'];
                }
            } elseif (!empty($this->{$attribute})) {
                $array[$attribute] = $this->{$attribute};
            }
        }

        return $array;
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->name = $this->getArrayKeySafe($array, 'name');
        $this->description = $this->getArrayKeySafe($array, 'description');
        $this->author = $this->getArrayKeySafe($array, 'author');
        $this->icon = preg_replace('/^oc\-/', 'wn-', $this->getArrayKeySafe($array, 'icon'));
        $this->homepage = $this->getArrayKeySafe($array, 'homepage');
        $this->replaces = array_map(
            function ($pluginCode, $versionConstraint) {
                return [
                    'plugin_code' => $pluginCode,
                    'version_constraint' => $versionConstraint,
                ];
            },
            array_keys($this->getArrayKeySafe($array, 'replaces', [])),
            array_values($this->getArrayKeySafe($array, 'replaces', []))
        );
    }

    protected function beforeCreate()
    {
        $this->localizedName = $this->name;
        $this->localizedDescription = $this->description;

        $pluginCode = strtolower($this->author_namespace.'.'.$this->namespace);

        $this->name = $pluginCode.'::lang.plugin.name';
        $this->description = $pluginCode.'::lang.plugin.description';
    }

    protected function afterCreate()
    {
        try {
            $this->initPluginStructure();
            $this->forcePluginRegistration();
            $this->initBuilderSettings();
        }
        catch (Exception $ex) {
            $this->rollbackPluginCreation();
            throw $ex;
        }
    }

    protected function initPluginStructure()
    {
        $basePath = $this->getPluginPath();

        $defaultLanguage = LocalizationModel::getDefaultLanguage();

        $structure = [
            $basePath.'/Plugin.php' => 'plugin.php.tpl',
            $basePath.'/updates/version.yaml' => 'version.yaml.tpl',
            $basePath.'/classes',
            $basePath.'/lang/'.$defaultLanguage.'/lang.php' => 'lang.php.tpl'
        ];

        $variables = [
            'authorNamespace' => $this->author_namespace,
            'pluginNamespace' => $this->namespace,
            'pluginNameSanitized' => $this->sanitizePHPString($this->localizedName),
            'pluginDescriptionSanitized' => $this->sanitizePHPString($this->localizedDescription),
        ];

        $generator = new FilesystemGenerator('$', $structure, '$/winter/builder/classes/pluginbasemodel/templates');
        $generator->setVariables($variables);
        $generator->generate();
    }

    protected function forcePluginRegistration()
    {
        PluginManager::instance()->loadPlugins();
        UpdateManager::instance()->update();
    }

    protected function rollbackPluginCreation()
    {
        $basePath = '$/'.$this->getPluginPath();
        $basePath = File::symbolizePath($basePath);

        if (basename($basePath) == strtolower($this->namespace)) {
            File::deleteDirectory($basePath);
        }
    }

    protected function sanitizePHPString($str)
    {
        return str_replace("'", "\'", $str);
    }

    /**
     * Returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        return $this->getPluginPathObj()->toPluginFilePath();
    }

    protected function getPluginPath()
    {
        return $this->getPluginPathObj()->toFilesystemPath();
    }

    protected function getPluginPathObj()
    {
        return new PluginCode($this->getPluginCode());
    }

    protected function initBuilderSettings()
    {
        // Initialize Builder configuration - author name and namespace
        // if it was not set yet.

        $settings =  PluginSettings::instance();
        if (strlen($settings->author_name) || strlen($settings->author_namespace)) {
            return;
        }

        $settings->author_name = $this->author;
        $settings->author_namespace = $this->author_namespace;

        $settings->save();
    }
}
