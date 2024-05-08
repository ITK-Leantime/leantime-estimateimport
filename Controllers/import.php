<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

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
   * @return void
   */
    public function init(ImportHelper $importHelper)
    {
        $this->importHelper = $importHelper;
    }
  /**
   * get
   *
   * @return Response
   *
   * @throws \Exception
   */
    public function get(): Response
    {
        unset($_SESSION['csv_data']);

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        $projectData = $this->importHelper->getAllProjectIds();
        $this->tpl->assign('currentProject', $_SESSION['currentProject']);
        $this->tpl->assign('projectData', $projectData);

        return $this->tpl->display('EstimateImport.import');
    }

  /**
   * post
   *
   * @param array<string, string|int> $params
   * @return void
   */
    public function post(array $params): void
    {
      // Read uploaded csv file
        $fileEncoding = $params['fileEncoding'];
        $estimateFile = fopen($_FILES['estimateFile']['tmp_name'], 'r, ' . $fileEncoding);

        if (!$estimateFile) {
            die('Unable to open file.');
        }

      // Loop rows and grab content
        $estimateData = [];
        $delimiter = $params['delimiter'];

        while (($row = fgetcsv($estimateFile, 0, $delimiter)) !== false) {
            $estimateData[] = $row;
        }
        fclose($estimateFile);

        $estimateHeaders = array_shift($estimateData);

      // Replace numeric array keys with headers from data
        $estimateDataResult = array_map(function ($item) use ($estimateHeaders) {
          // Remove empty headers
            $filteredHeaders = array_filter($estimateHeaders, function ($estimateHeader) {
                return !empty(trim($estimateHeader));
            });
            $itemWithHeaders = array_combine($filteredHeaders, array_intersect_key($item, $filteredHeaders));
            return $itemWithHeaders;
        }, $estimateData);

      // Save data to session
        $_SESSION['csv_data']['data'] = $estimateDataResult;
        $_SESSION['csv_data']['headers'] = $estimateHeaders;
        $_SESSION['csv_data']['project_id'] = $params['projectId'];
        $_SESSION['csv_data']['date_format'] = $params['dateFormat'];

      // Redirect to next step
        header('Location: /EstimateImport/importMapping');
    }
}
