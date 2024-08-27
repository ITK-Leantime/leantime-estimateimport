<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Leantime\Core\Controller;
use Leantime\Core\Template;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Plugins\EstimateImport\Services\ImportHelper as ImportHelper;

/**
 * ImportMapping class
 */
class ImportMapping extends Controller
{
    private ImportHelper $importHelper;
    protected Template $template;

  /**
   * constructor
   *
   * @param ImportHelper $importHelper
   *
   * @return void
   */
    public function init(ImportHelper $importHelper, Template $template): void
    {
        $this->importHelper = $importHelper;
        $this->template = $template;
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
        $csvDataFile = session('csv_data.temp_fileName');

        $csvData = $this->importHelper->getDataFromTempFile($csvDataFile);

        $estimateHeaders = $csvData['headers'] ?? [];
        $estimateDataResult = $csvData['data'] ?? [];

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->template->assign('importStyling', $importStyling);
        $this->template->assign('importScript', $importScript);

        $supportedFields = $this->importHelper->getSupportedFields();
        $this->template->assign('supportedFields', $supportedFields);
        $this->template->assign('estimateFileHeaders', $estimateHeaders);
        $this->template->assign('estimateFileData', $estimateDataResult);


        return $this->template->display('EstimateImport.importMapping');
    }

  /**
   * Handles submitted mapping and stores it in the tmp file.
   *
   * @param array<string, string|int> $params
   * @return RedirectResponse
   */
    public function post(array $params): RedirectResponse
    {
        $csvDataFile = session('csv_data.temp_fileName');

        // @TODO validate mapping
        $csvData['mapping_data'] = $params;

        $this->importHelper->saveDataToTempFile($csvData, $csvDataFile);

        // Redirect to next step
        return new RedirectResponse('/EstimateImport/importValidation');
    }
}
