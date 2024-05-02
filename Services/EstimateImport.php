<?php

namespace Leantime\Plugins\EstimateImport\Services;

/**
 * DataExport plugin.
 */
class EstimateImport
{
    private static $assets = [
        // source => target
        __DIR__ . '/../assets/EstimateImport.js' => APP_ROOT . '/public/dist/js/plugin-EstimateImport.js',
        __DIR__ . '/../assets/EstimateImport.css' => APP_ROOT . '/public/dist/css/plugin-EstimateImport.css',
    ];

    /**
     * Install plugin.
     *
     * @return void
     */
    public function install(): void
    {
        foreach (static::$assets as $source => $target) {
            if (file_exists($target)) {
                unlink($target);
            }
            symlink($source, $target);
        }
    }

    /**
     * Uninstall plugin.
     *
     * @return void
     */
    public function uninstall(): void
    {
        foreach (static::$assets as $target) {
            if (file_exists($target)) {
                unlink($target);
            }
        }
    }
}
