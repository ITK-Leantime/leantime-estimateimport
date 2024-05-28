<?php

namespace Leantime\Plugins\EstimateImport\Services;

/**
 * EstimateImport plugin.
 */
class EstimateImport
{
    /**
     * @var array<string, string> $assets
     */
    private static array $assets = [
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
        foreach (self::getAssets() as $source => $target) {
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
        foreach (self::getAssets() as $target) {
            if (file_exists($target)) {
                unlink($target);
            }
        }
    }

    /**
     * Get assets
     *
     * @return array|string[]
     */
    private static function getAssets(): array
    {
        return self::$assets;
    }
}
