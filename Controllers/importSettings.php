<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Plugins\EstimateImport\Repositories\ImportSettings as ImportSettingsRepository;

/**
 * ImportSettings class
 */
class ImportSettings extends Controller
{
    private ImportSettingsRepository $importSettingsRepo;

  /**
   * constructor
   *
   * @param ImportSettingsRepository $importSettingsRepo
   * @return void
   */
    public function init(ImportSettingsRepository $importSettingsRepo)
    {
        $this->importSettingsRepo = $importSettingsRepo;
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
        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        $projectData = $this->importSettingsRepo->getAllProjectIds();
        $this->tpl->assign('currentProject', $_SESSION['currentProject']);
        $this->tpl->assign('projectData', $projectData);
        return $this->tpl->display('EstimateImport.importSettings');
    }

  /**
   * post
   *
   * @param array $params
   * @return void
   */
    public function post(array $params): void
    {

        $estimateData = $_SESSION['csv_data'];
        $estimateDataHeaders = array();
        $estimateDataData = array();
        foreach ($estimateData as $count => $row) {
            if ($count === 0) {
                $estimateDataHeaders = $row;
            } else {
                $estimateDataData[] = $row;
            }
        }


      // Replace numeric array keys with headers from data
        $estimateDataResult = [];

        foreach ($estimateDataData as $item) {
            $temp = [];

            foreach ($estimateDataHeaders as $i => $header) {
                $header = trim($header);

                if (!empty($header)) {
                    $temp[$header] = $item[$i];
                } else { // Remove entry if empty
                    unset($temp[$header]);
                }
            }

            $estimateDataResult[] = $temp;
        }
        $_SESSION['csv_data']['data'] = $estimateDataResult;

        $_SESSION['csv_data']['headers'] = $estimateDataHeaders;
        $_SESSION['csv_data']['project_id'] = $params['projectId'];

        header('Location: /EstimateImport/importMapping');
    }
}
