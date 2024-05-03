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
        if ($_GET['fixErrors']) {
            $this->fixErrors($_GET['fixErrors']);
        }
        $this->importHelper->dataValidationCheck();

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        $supportedFields = $this->importHelper->getSupportedFields();
        $this->tpl->assign('supportedFields', $supportedFields);

        $dataToValidate = $_SESSION['csv_data']['result'];
        $this->tpl->assign('dataToValidate', $dataToValidate);

        $mappings = $_SESSION['csv_data']['mappings'];
        $this->tpl->assign('mappings', $mappings);

        $milestones = $_SESSION['csv_data']['milestones'];
        $this->tpl->assign('milestones', $milestones);

        $validationWarnings = $_SESSION['csv_data']['warnings'];
        $this->tpl->assign('validationWarnings', $validationWarnings);

        $validationErrors = $_SESSION['csv_data']['errors'];
        $this->tpl->assign('validationErrors', $validationErrors);

        return $this->tpl->display('EstimateImport.importValidation');
    }

  /**
     * Attempts to fix certain errors given in import validation
     *
     * @return void
     *
     */
    public function fixErrors($subject)
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
            break;
            default:
                die('not implemented');
            break;
        }
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

        foreach ($dataToImport as $count => $datumToImport) {
            echo $count;
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
                        $values[$mappings[$key]] = $date->format($leantimeUserDateFormat) ?? '';
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
        header('Location: /projects/changeCurrentProject/' . $currentProject."?estimateImportSuccess=1");
    }
}
