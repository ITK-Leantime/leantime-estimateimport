<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Exception;
use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Plugins\EstimateImport\Services\ImportHelper as ImportHelper;

/**
 * Import class
 */
class Import extends Controller
{
    private ImportHelper $importHelper;

    /**
     * constructor
     *
     * @param ImportHelper $importHelper
     *
     * @return void
     */
    public function init(ImportHelper $importHelper): void
    {
        $this->importHelper = $importHelper;
    }

    /**
     * Gathers data and feeds it to the template.
     *
     * @return Response
     *
     * @throws Exception
     */
    public function get(): Response
    {
        unset($_SESSION['csv_data']['temp_fileName']);

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        $projectData = $this->importHelper->getAllProjectIds();

        // Get current project set in Leantime session
        $currentProject = $_SESSION['currentProject'];

        if (isset($currentProject)) {
            $this->tpl->assign('currentProject', $_SESSION['currentProject']);
        }

        $this->tpl->assign('projectData', $projectData);

        return $this->tpl->display('EstimateImport.import');
    }

    /**
     * Handles the data submitted.
     * Reads submitted file, settings and stores data in tmp file.
     *
     * @param array<string, string|int> $params
     *
     * @return void
     *
     * @throws Exception
     */
    public function post(array $params): void
    {
        $estimateFileType = pathinfo($_FILES['estimateFile']['name'], PATHINFO_EXTENSION);
        $estimateFile = $_FILES['estimateFile']['tmp_name'];

        if ($estimateFileType !== 'csv' || !$estimateFile) {
            error_log('No file or wrong extension');
            throw new Exception('No file or wrong extension');
        }
        $estimateFileReader = fopen($estimateFile, 'r');

        if (!$estimateFileReader) {
            error_log('Cannot open file');
            throw new Exception('Cannot open file');
        }

        // Loop rows and grab content
        $estimateData = [];
        $delimiter = $params['delimiter'] ?? ';';

        while (($row = fgetcsv($estimateFileReader, 0, $delimiter)) !== false) {
            $estimateData[] = $row;
        }
        fclose($estimateFileReader);

        if (empty($estimateData)) {
            error_log('Cannot open file');
            throw new Exception('Cannot open file');
        }
        $estimateHeaders = array_shift($estimateData);

        // Replace numeric array keys with headers from data
        $estimateDataResult = array_map(function ($item) use ($estimateHeaders) {
            // Remove empty headers
            $filteredHeaders = array_filter($estimateHeaders, function ($estimateHeader) {
                return !empty(trim($estimateHeader));
            });
            return array_combine($filteredHeaders, array_intersect_key($item, $filteredHeaders));
        }, $estimateData);

        $dataToStore = [
            'data' => $estimateDataResult,
            'headers' => $estimateHeaders,
            'projectId' => $params['projectId'],
            'dateFormat' => $params['dateFormat'],
        ];

        $tmpFile = $this->importHelper->saveDataToTempFile($dataToStore);

        // Save tmp file location to session
        $_SESSION['csv_data']['temp_fileName'] = $tmpFile;

        // Redirect to next step
        header('Location: /EstimateImport/importMapping');
    }
}
