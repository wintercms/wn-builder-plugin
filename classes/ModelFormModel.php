<?php namespace Winter\Builder\Classes;

use SystemException;
use ValidationException;

/**
 * Represents and manages model forms.
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 * @author Winter CMS
 */
class ModelFormModel extends ModelYamlModel
{
    public $controls;

    protected static $fillable = [
        'fileName',
        'controls'
    ];

    protected $validationRules = [
        'fileName' => ['required', 'regex:/^[a-z0-9\.\-_]+$/i']
    ];

    public function loadForm($path)
    {
        $this->fileName = $path;

        return parent::load($this->getFilePath());
    }

    public function fill(array $attributes)
    {
        if (!is_array($attributes['controls'])) {
            $attributes['controls'] = json_decode($attributes['controls'], true);

            if ($attributes['controls'] === null) {
                throw new SystemException('Cannot decode controls JSON string.');
            }
        }

        return parent::fill($attributes);
    }

    /**
     * Determines the fields that should be stored as "jsonable" data.
     *
     * @return array
     */
    public function getJsonableFields()
    {
        $fields = [];

        // Search outside fields
        if (isset($this->controls['fields']) && count($this->controls['fields'])) {
            $this->scanJsonableFields($fields, $this->controls['fields']);
        }

        // Search primary tabs fields
        if (isset($this->controls['tabs']['fields']) && count($this->controls['tabs']['fields'])) {
            $this->scanJsonableFields($fields, $this->controls['tabs']['fields']);
        }

        // Search secondary tabs fields
        if (isset($this->controls['secondaryTabs']['fields']) && count($this->controls['secondaryTabs']['fields'])) {
            $this->scanJsonableFields($fields, $this->controls['secondaryTabs']['fields']);
        }

        return $fields;
    }

    /**
     * Scans for "jsonable" fields within a fieldset and adds them to the found array.
     *
     * @param array $found
     * @param array $fields
     * @return void
     */
    protected function scanJsonableFields(array &$found, array $fields)
    {
        $jsonableFields = [
            'checkboxlist',
            'datatable',
            'nestedform',
            'repeater',
        ];

        foreach ($fields as $name => $field) {
            // Skip related fields
            if (str_contains($name, '[')) {
                continue;
            }

            if (in_array($field['type'], $jsonableFields)) {
                if (!array_key_exists($name, $found)) {
                    $found[] = $name;
                    continue;
                }
            }

            // Allow for multi-selection dropdowns
            if ($field['type'] === 'dropdown' && $field['multiple'] === true) {
                if (!array_key_exists($name, $found)) {
                    $found[] = $name;
                    continue;
                }
            }
        }
    }

    public static function validateFileIsModelType($fileContentsArray)
    {
        $modelRootNodes = [
            'fields',
            'tabs',
            'secondaryTabs'
        ];

        foreach ($modelRootNodes as $node) {
            if (array_key_exists($node, $fileContentsArray)) {
                return true;
            }
        }

        return false;
    }

    public function validate()
    {
        parent::validate();

        if (!$this->controls) {
            throw new ValidationException(['controls' => 'Please create at least one field.']);
        }
    }

    public function initDefaults()
    {
        $this->fileName = 'fields.yaml';
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        return $this->controls;
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->controls = $array;
    }
}
