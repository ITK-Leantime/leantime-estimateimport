<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Plugins\EstimateImport\Services\ImportHelper as ImportHelper;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * ImportValidation class
 */
class ImportValidation extends Controller
{
    private TicketService $ticketService;
    private ImportHelper $importHelper;

    /**
     * constructor
     *
     * @param TicketService $ticketService
     * @param ImportHelper  $importHelper
     * @return void
     */
    public function init(TicketService $ticketService, ImportHelper $importHelper)
    {
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
        // Javascript fixErrors hook
        if ($_GET['fixErrors']) {
            $this->fixErrors($_GET['fixErrors']);
        }

        // Check data validitity
        $this->importHelper->dataValidationCheck();

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        // Get supported fields to display human-friendly field names
        $supportedFields = $this->importHelper->getSupportedFields();
        $this->tpl->assign('supportedFields', $supportedFields);

        // Data to display
        $dataToValidate = $_SESSION['csv_data']['result'];
        $this->tpl->assign('dataToValidate', $dataToValidate);

        // Previously mapped fields
        $mappings = $_SESSION['csv_data']['mappings'];
        $this->tpl->assign('mappings', $mappings);

        // Warnings gathered in dataValidationCheck
        $validationWarnings = $_SESSION['csv_data']['warnings'];
        $this->tpl->assign('validationWarnings', $validationWarnings);

        // Errors gathered in dataValidationCheck
        $validationErrors = $_SESSION['csv_data']['errors'];
        $this->tpl->assign('validationErrors', $validationErrors);

        return $this->tpl->display('EstimateImport.importValidation');
    }

    /**
     * post
     *
     * @param array<string, array<int, int>|string> $params
     * @return void
     */
    public function post(array $params): void
    {
        $mappings = $_SESSION['csv_data']['mappings'];
        $dataToImport = $_SESSION['csv_data']['result'];
        $currentProject = $_SESSION['currentProject'];
        $dataImportConfirmation = $params['dataImportConfirmation'];

        foreach ($dataToImport as $count => $datumToImport) {
            if (!in_array($count, $dataImportConfirmation)) {
                continue;
            }
            $values = array();
            foreach ($datumToImport as $key => $dat) {
                $key = str_replace(' ', '_', $key);

                switch ($mappings[$key]) {
                    // If milestone is mapped, get and set id if exists. Else do not set.
                    case 'milestoneid':
                        $milestoneId = $this->importHelper->checkMilestoneExist($dat, true);
                        if ($milestoneId) {
                            $values['milestoneid'] = $milestoneId;
                        }
                        break;
                    case 'planHours':
                        $values[$mappings[$key]] = str_replace(',', '.', $dat); // Convert comma to punctuation
                        $values[$mappings[$key]] = str_replace(',', '.', $dat); // Set hourRemaining aswell as it is not set automatically.
                        break;
                    // If dateToFinish is mapped, convert date from known format to db format.
                    case 'dateToFinish':
                        $dateFormat = $_SESSION['csv_data']['date_format'];
                        $date = \DateTime::createFromFormat($dateFormat, $dat);

                        // Because of Leantimes internal "date database preparation", dates has to be formatted like datetimehelper expects
                        $leantimeUserDateFormat = $_SESSION['usersettings.language.date_format'] ?? $this->language->__('language.dateformat');
                        $values[$mappings[$key]] = $date->format($leantimeUserDateFormat);
                        break;
                    default:
                        $values[$mappings[$key]] = $dat ?? '';
                        break;
                }

                $values['status'] = '3'; // Status "3" is "New"
                $values['currentProject'] = $currentProject; // Current project id gathered from entry point (project dashboard).
            }
            $this->ticketService->addTicket($values);
        }

        unset($_SESSION['csv_data']);
        header('Location: /projects/changeCurrentProject/' . $currentProject . '?estimateImportSuccess=1');
    }

     /**
     * Attempts to fix certain errors given in import validation
     *
     * @return void
     *
     */
    public function fixErrors(string $subject): void
    {
        switch ($subject) {
            case 'Milestone':
                $milestonesToCreate = $_SESSION['csv_data']['errors'][$subject];
                $result = array();
                foreach ($milestonesToCreate as $milestone => $errorMessage) {
                    $milestoneExists = $this->importHelper->checkMilestoneExist($milestone, true);
                    if (!$milestoneExists) {
                        $milestoneAdded = $this->importHelper->addMilestone($milestone);
                        $result[$milestone] = $milestoneAdded;
                    }
                }
                die(json_encode($result));
            default:
                die('not implemented');
        }
    }
}
