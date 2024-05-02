<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

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
        unset($_SESSION['csv_data']['warnings']);
        unset($_SESSION['csv_data']['errors']);
        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        $supportedFields = $this->importHelper->getSupportedFields();
        $this->tpl->assign('supportedFields', $supportedFields);
        $this->tpl->assign('estimateFileHeaders', $_SESSION['csv_data']['headers']);
        $this->tpl->assign('estimateFileData', $_SESSION['csv_data']['data']);


        return $this->tpl->display('EstimateImport.importMapping');
    }

  /**
   * post
   *
   * @param array $params
   * @return void
   */
    public function post(array $params): void
    {
        $data = $_SESSION['csv_data']['data'];
        foreach ($params as $key => $param) {
            $key = str_replace('_', ' ', $key);
            foreach ($data as $data_key => &$datum) {
                if (trim($param) === '-1') {
                    unset($datum[$key]);

                    if (empty($datum)) {
                        $_SESSION['csv_data']['warnings'][] = 'Ticket ' . $data_key . ': Was removed due to being empty.';
                        unset($data[$data_key]);
                    }
                    continue;
                }
                if (empty($datum[$key])) {
                    $_SESSION['csv_data']['warnings'][] = 'Ticket ' . $data_key . ': "' . $param . '" empty and was removed.';
                    unset($datum[$key]);

                    if (empty($datum)) {
                        $_SESSION['csv_data']['warnings'][] = 'Ticket ' . $data_key . ': Was removed due to being empty.';
                        unset($data[$data_key]);
                        continue;
                    }
                }
                if ($param === 'milestoneid') {
                    if (empty($datum[$key])) {
                        continue;
                    }
                    $exists = $this->importHelper->checkMilestoneExist($datum[$key]);
                    if (!$exists) {
                        $_SESSION['csv_data']['errors']['Milestone'][$datum[$key]] = 'The following milestone does not exist: ';
                    }
                }
            }
        }

        $_SESSION['csv_data']['mappings'] = $params;
        $_SESSION['csv_data']['result'] = $data;

        header('Location: /EstimateImport/importValidation');
    }
}
