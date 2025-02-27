<?php namespace {{ pluginNamespace }}\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class {{ controller }} extends Controller
{
    public $implement = [
        {{ behaviors|map(b => "'" ~ b ~ "'")|join(",\n        ")|raw }}
    ];
{{ templateParts|raw }}
    public function __construct()
    {
        parent::__construct();
{% if menuItem %}
{% if not sideMenuItem %}
        BackendMenu::setContext('{{ pluginCode }}', '{{ menuItem }}');
{% else %}
        BackendMenu::setContext('{{ pluginCode }}', '{{ menuItem }}', '{{ sideMenuItem }}');
{% endif %}
{% endif %}
    }
}
