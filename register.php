<?php

use Leantime\Core\Events;

/**
* Adds a menu point for adding fixture data.
* @param array $menuStructure The existing menu structure to which the new item will be added.
* @return array The modified menu structure with the new item added.
*/
function addImportDataMenuPoint(array $menuStructure): array
{
    $menuStructure['default'][60] = ['type' => 'item', 'module' => 'pluginTemplate', 'title' => 'Importer To-Dos', 'icon' => 'fa', 'tooltip' => 'Generate Fixture Data', 'href' => '/EstimateImport/import', 'active' => ['settings']];
    return $menuStructure;
}

Events::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures', 'addImportDataMenuPoint');
