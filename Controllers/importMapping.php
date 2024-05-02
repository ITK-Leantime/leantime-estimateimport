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
        $_SESSION['csv_data']['mapping_data'] = $params;
        header('Location: /EstimateImport/importValidation');
    }
}
