<?php

use Leantime\Core\Events;


function addFixtureDataMenuPointHest($menuStructure)
{
    $menuStructure['default'][60] = ['type' => 'item', 'module' => 'pluginTemplate', 'title' => 'Importer To-Dos', 'icon' => 'fa', 'tooltip' => 'Generate Fixture Data', 'href' => '/EstimateImport/import', 'active' => ['settings']];
    return $menuStructure;

}

Events::add_filter_listener("leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures", 'addFixtureDataMenuPointHest');
