<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;

use Leantime\Plugins\EstimateImport\Repositories\Import as ImportRepository;

/**
 * Import class
 */
class Import extends Controller
{
    private ImportRepository $importRepository;

  /**
   * constructor
   *
   * @param ImportRepository $importRepository
   * @return void
   */
    public function init(ImportRepository $importRepository)
    {
        $this->importRepository = $importRepository;
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

        $projectData = $this->importRepository->getAllProjectIds();
        $this->tpl->assign('currentProject', $_SESSION['currentProject']);
        $this->tpl->assign('projectData', $projectData);

        return $this->tpl->display('EstimateImport.import');
    }

    /**
     * post
     *
     * @param array $params
     * @return void
     */
    public function post(array $params): void
    {
        $fileEncoding = $params['fileEncoding'];

        $estimateFile = fopen($_FILES['estimateFile']['tmp_name'], 'r, ' . $fileEncoding);

        if (!$estimateFile) {
            die('Unable to open file.');
        }

        $estimateData = array();

        $delimiter = $params['delimiter'];

        while (($row = fgetcsv($estimateFile, 0, $delimiter)) !== false) {
            $estimateData[] = $row;
        }

        fclose($estimateFile);

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
        $_SESSION['csv_data']['date_format'] = $params['dateFormat'];

        header('Location: /EstimateImport/importMapping');
    }
}
