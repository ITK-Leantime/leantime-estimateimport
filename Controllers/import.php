<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;

class Import extends Controller
{

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

        $importStyling = dirname(dirname($_SERVER["DOCUMENT_ROOT"])) . "dist/css/plugin-EstimateImport.css";
        $importScript = dirname(dirname($_SERVER["DOCUMENT_ROOT"])) . "dist/js/plugin-EstimateImport.js";
        $this->tpl->assign("importStyling", $importStyling);
        $this->tpl->assign("importScript", $importScript);


        return $this->tpl->display("EstimateImport.import");
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

        $estimateFile = fopen($_FILES['estimateFile']["tmp_name"], 'r, ' . $fileEncoding);

        if (!$estimateFile) {
            die("Unable to open file.");
        }

        $csvData = array();

        $delimiter = $params['delimiter'];

        while (($row = fgetcsv($estimateFile, 0, $delimiter)) !== false) {
            $csvData[] = $row;
        }

        fclose($estimateFile);

        $_SESSION['csv_data'] = $csvData;

        header("Location: /EstimateImport/importSettings");
    }
}
