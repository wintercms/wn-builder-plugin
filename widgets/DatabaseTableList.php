<?php namespace Winter\Builder\Widgets;

use Str;
use Input;
use Backend\Classes\WidgetBase;
use Winter\Builder\Classes\DatabaseTableModel;

/**
 * Database table list widget.
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DatabaseTableList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\SelectableWidget;

    protected $theme;

    public $noRecordsMessage = 'winter.builder::lang.database.no_records';
    public $unlinkedTableMessage = 'winter.builder::lang.database.unlinked_table';
    public $linkedModelMessage = 'winter.builder::lang.database.linked_model';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body', $this->getRenderData());
    }

    public function updateList()
    {
        return ['#'.$this->getId('database-table-list') => $this->makePartial('items', $this->getRenderData())];
    }

    public function refreshActivePlugin()
    {
        return ['#'.$this->getId('body') => $this->makePartial('widget-contents', $this->getRenderData())];
    }

    /*
     * Event handlers
     */

    public function onUpdate()
    {
        return $this->updateList();
    }

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        return $this->updateList();
    }

    /*
     * Methods for the internal use
     */

    protected function getData($pluginVector)
    {
        if (!$pluginVector) {
            return [];
        }

        $pluginCode = $pluginVector->pluginCodeObj->toCode();

        if (!$pluginCode) {
            return [];
        }

        $tables = $this->getTableList($pluginCode);
        $searchTerm = Str::lower($this->getSearchTerm());

        // Apply the search
        //
        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $result = [];

            foreach ($tables as $table) {
                if ($this->textMatchesSearch($words, $table)) {
                    $result[] = $table;
                }
            }

            $tables = $result;
        }

        return $tables;
    }

    protected function getTableList($pluginCode)
    {
        $result = DatabaseTableModel::listPluginTables($pluginCode);

        return $result;
    }

    protected function getRenderData()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();

        return [
            'pluginVector'=>$activePluginVector,
            'items'=>$this->getData($activePluginVector)
        ];
    }
}
