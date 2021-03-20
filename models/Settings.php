<?php namespace Winter\Builder\Models;

use Winter\Storm\Database\Model;

/**
 * Builder settings model
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class Settings extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'winter_builder_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation rules
     */
    public $rules = [
        'author_name' => 'required',
        'author_namespace' => ['required', 'regex:/^[a-z]+[a-z0-9]+$/i', 'reserved']
    ];
}
