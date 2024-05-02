<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Plugins\EstimateImport\Services\ImportHelper as ImportHelper;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

class ImportValidation extends Controller
{

    private TicketService $ticketService;
    private ImportHelper $importHelper;

    /**
     * constructor
     *
     * @param TicketService $ticketService
     * @param ImportHelper $importHelper
     * @return void
     */
    public function init(TicketService $ticketService, ImportHelper $importHelper) {
      $this->ticketService = $ticketService;
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
        $importStyling = dirname(dirname($_SERVER["DOCUMENT_ROOT"])) . "dist/css/plugin-EstimateImport.css";
        $importScript = dirname(dirname($_SERVER["DOCUMENT_ROOT"])) . "dist/js/plugin-EstimateImport.js";
        $this->tpl->assign("importStyling", $importStyling);
        $this->tpl->assign("importScript", $importScript);

        $supportedFields = $this->importHelper->getSupportedFields();
        $this->tpl->assign("supportedFields", $supportedFields);

        $dataToValidate = $_SESSION['csv_data']['result'];
        $this->tpl->assign("dataToValidate", $dataToValidate);

        $mappings = $_SESSION['csv_data']['mappings'];
        $this->tpl->assign("mappings", $mappings);

        $milestones = $_SESSION['csv_data']['milestones'];
        $this->tpl->assign("milestones", $milestones);

        $validationWarnings = $_SESSION['csv_data']['warnings'];
        $this->tpl->assign("validationWarnings", $validationWarnings);

        $validationErrors = $_SESSION['csv_data']['errors'];
        $this->tpl->assign("validationErrors", $validationErrors);

        return $this->tpl->display("EstimateImport.importValidation");
    }

    /**
     * post
     *
     * @param array $params
     * @return void
     */
    public function post(array $params): void
    {
        $mappings = $_SESSION['csv_data']['mappings'];
        $dataToImport = $_SESSION['csv_data']['result'];
        $currentProject = $_SESSION['currentProject'];

        foreach($dataToImport as $datumToImport) {
        $values = array();
            foreach($datumToImport as $key => $dat) {
                $key = str_replace(' ', '_', $key);

                // If milestone is mapped, get and set id if exists. Else do not set.
                if ($mappings[$key] === "milestoneid") {
                    $milestoneId = $this->importHelper->checkMilestoneExist($dat);
                    if ($milestoneId) {
                        $values['milestoneid'] = $milestoneId;
                    }
                } else {
                    $values[$mappings[$key]] = $dat ?? "";
                }

                $values['status'] = "3"; // Status "3" is "New"
                $values['currentProject'] = $currentProject; // Current project id gathered from entry point (project dashboard).

            }
            $values['planHours'] = str_replace(",", ".", $values['planHours']); // Convert comma to punctuation
            $values['hourRemaining'] = str_replace(",", ".", $values['planHours']); // Set hourRemaining aswell as it is not set automatically.
            $this->ticketService->addTicket($values);

        }


        header('Location: /projects/changeCurrentProject/'. $currentProject);
    }
}
