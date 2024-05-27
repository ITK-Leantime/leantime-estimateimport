<?php

namespace Leantime\Plugins\EstimateImport\Controllers;

use Leantime\Core\Controller;
use Leantime\Core\Support\DateTimeHelper;
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
     *
     * @param ImportHelper  $importHelper
     *
     * @return void
     */
    public function init(TicketService $ticketService, ImportHelper $importHelper): void
    {
        $this->ticketService = $ticketService;
        $this->importHelper = $importHelper;
    }
    /**
     * Gathers data and feeds it to the template.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function get(): Response
    {
        $csvDataFile = $_SESSION['csv_data']['temp_fileName'];

        $csvData = $this->importHelper->getDataFromTempFile($csvDataFile);

        // Javascript fixErrors hook
        if ($_GET['fixErrors']) {
            $this->fixErrors($_GET['fixErrors'], $csvData);
        }

        // Check data validity
        $validatedData = $this->importHelper->dataValidationCheck($csvDataFile, $csvData);

        if (!$validatedData) {
            error_log('Validation failed');
            throw new \Exception('Validation failed');
        }

        $importStyling = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/css/plugin-EstimateImport.css';
        $importScript = dirname($_SERVER['DOCUMENT_ROOT'], 2) . 'dist/js/plugin-EstimateImport.js';
        $this->tpl->assign('importStyling', $importStyling);
        $this->tpl->assign('importScript', $importScript);

        // Get supported fields to display human-friendly field names
        $supportedFields = $this->importHelper->getSupportedFields();
        $this->tpl->assign('supportedFields', $supportedFields);

        // Data to display
        $dataToValidate = $validatedData['data'] ?? [];
        $this->tpl->assign('dataToValidate', $dataToValidate);

        // Previously mapped fields
        $mappings = $validatedData['mapping_data'] ?? [];
        $this->tpl->assign('mappings', $mappings);

        // Warnings gathered in dataValidationCheck
        $validationWarnings = $validatedData['warnings'] ?? [];
        $this->tpl->assign('validationWarnings', $validationWarnings);

        // Errors gathered in dataValidationCheck
        $validationErrors = $validatedData['errors'] ?? [];
        $this->tpl->assign('validationErrors', $validationErrors);

        return $this->tpl->display('EstimateImport.importValidation');
    }

    /**
     * Gathers the validated data, and imports it into Leantime.
     *
     * @param array<string, array<int, int>|string> $params
     *
     * @return void
     *
     * @throws \Exception
     */
    public function post(array $params): void
    {
        $csvDataFile = $_SESSION['csv_data']['temp_fileName'];
        $csvData = $this->importHelper->getDataFromTempFile($csvDataFile);

        if (!$csvData) {
            error_log('Could not find data from csv file');
            throw new \Exception('Could not find data from csv file');
        }

        $mappings = $csvData['mapping_data'] ?? [];
        $dataToImport = $csvData['data'] ?? [];
        $currentProject = $_SESSION['currentProject'] ?? $csvData['projectId'];
        $dateFormat = $csvData['dateFormat'] ?? 'Y-m-d';
        $dataImportConfirmation = $params['dataImportConfirmation'];

        if (!$currentProject) {
            error_log('Could not find current project');
            throw new \Exception('Could not find current project');
        }
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
                        $milestoneId = $this->importHelper->checkMilestoneExist($dat, $currentProject);
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
                        $date = \DateTime::createFromFormat($dateFormat, $dat);

                        // Because of Leantimes internal "date database preparation", dates has to be formatted like datetimehelper expects
                        $leantimeUserDateFormat = $_SESSION['usersettings.language.date_format'] ?? $this->language->__('language.dateformat');
                        $values[$mappings[$key]] = $date->format($leantimeUserDateFormat);
                        break;
                    default:
                        $values[$mappings[$key]] = $dat ?? '';
                        break;
                }
                // Status "3" is "New"
                $values['status'] = '3';
                $values['currentProject'] = $currentProject; // Current project id gathered from entry point (project dashboard).
            }
            $this->ticketService->addTicket($values);
        }

        if ($csvDataFile) {
            unlink($csvDataFile);
        }
        header('Location: /projects/changeCurrentProject/' . $currentProject . '?estimateImportSuccess=1');
    }

    /**
     * Attempts to fix certain errors given in import validation
     *
     * @param string $subject
     *
     * @param array $csvData
     *
     * @return void
     *
     * @throws \Exception
     */
    public function fixErrors(string $subject, array $csvData): void
    {

        if (!$csvData) {
            error_log('Trying to fix errors without providing tmp file data containing subjects');
            throw new \Exception('Trying to fix errors without providing tmp file data containing subjects');
        }

        $projectId = $csvData['projectId'];
        if (!$projectId) {
            error_log('Trying to fix errors without providing projectId');
            throw new \Exception('Trying to fix errors without providing projectId');
        }
        switch ($subject) {
            case 'Milestone':
                $milestonesToCreate = $csvData['errors'][$subject];
                $result = array();
                foreach ($milestonesToCreate as $milestone => $errorMessage) {
                    $milestoneExists = $this->importHelper->checkMilestoneExist($milestone, $projectId, false);
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
