<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Exception;
use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Plugins\EstimateImport\Services\ImportHelper as ImportHelper;

/**
 * ImportMapping class
 */
class ImportMapping extends Controller
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
        $csvDataFile = $_SESSION['csv_data']['temp_fileName'];

        $csvData = $this->importHelper->getDataFromTempFile($csvDataFile);

        $estimateHeaders = $csvData['headers'] ?? [];
        $estimateDataResult = $csvData['data'] ?? [];

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        $supportedFields = $this->importHelper->getSupportedFields();
        $this->tpl->assign('supportedFields', $supportedFields);
        $this->tpl->assign('estimateFileHeaders', $estimateHeaders);
        $this->tpl->assign('estimateFileData', $estimateDataResult);


        return $this->tpl->display('EstimateImport.importMapping');
    }

  /**
   * Handles submitted mapping and stores it in the tmp file.
   *
   * @param array<string, string|int> $params
   * @return void
   */
    public function post(array $params): void
    {
        $csvDataFile = $_SESSION['csv_data']['temp_fileName'];

        // @TODO validate mapping
        $csvData['mapping_data'] = $params;

        $this->importHelper->saveDataToTempFile($csvData, $csvDataFile);

        // Redirect to next step
        header('Location: /EstimateImport/importValidation');
    }
}
