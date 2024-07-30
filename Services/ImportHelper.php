<?php

namespace Leantime\Plugins\EstimateImport\Services;

use DateTime;
use Exception;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

/**
 * ImportHelper class
 */
class ImportHelper
{
    /**
     * @var array<string, array<string, mixed>> $projectMilestones
     */
    private array $projectMilestones;

    /**
     * constructor
     *
     * @param TicketService  $ticketService
     * @param ProjectService $projectService
     * @return void
     */
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ProjectService $projectService,
    ) {
    }

    /**
     * Get milestone object internally
     *
     * @return array<string, array<string, string>>
     *
     * @throws Exception
     */
    private function getProjectMilestones(string $projectId, bool $useCache = true): array
    {
        if (!isset($this->projectMilestones) || !$useCache) {
            $this->setProjectMilestones($projectId);
        }
        return $this->projectMilestones;
    }

    /**
     * Set milestone object internally
     *
     * @param string $projectId
     *
     * @return void
     *
     * @throws Exception
     */
    private function setProjectMilestones(string $projectId): void
    {
        if (!$projectId) {
            error_log('Trying to get milestones without providing project id');
            throw new \Exception('Trying to get milestones without providing project id');
        }
        $milestoneData = $this->ticketService->getAllMilestones([
            'type' => 'milestone',
            'currentProject' => $projectId,
        ]);

        $this->projectMilestones = array_map(function ($milestone) {
            return [
                'id' => $milestone->id,
                'headline' => $milestone->headline,
            ];
        }, $milestoneData);
    }

    /**
     * Check if a given milestone exists in the database for the current project
     *
     * @param string $milestone
     *
     * @param string $projectId
     *
     * @param bool   $useCache
     *
     * @return bool|string
     *
     * @throws Exception
     */
    public function checkMilestoneExist(string $milestone, string $projectId, bool $useCache = true): bool|string
    {
        $milestones = $this->getProjectMilestones($projectId, $useCache);
        return array_reduce($milestones, function ($carry, $item) use ($milestone) {
            return $carry !== false ? $carry : ($item['headline'] === $milestone ? $item['id'] : false);
        }, false);
    }

    /**
     * Adds a given milestone
     *
     * @return array<string, string>|bool|int
     *
     */
    public function addMilestone(string $milestone): array|bool|int
    {
        $params = array(
            'headline' => $milestone,
            'tags' => '',
        );
        return $this->ticketService->quickAddMilestone($params);
    }

    /**
     * Gets all supported fields for mapping
     *
     * @return array<string, array<string, string>>
     *
     */
    public function getSupportedFields(): array
    {
        return array(
            'headline' => array(
                'name' => 'Title',
                'help' => '',
            ),
            'description' => array(
                'name' => 'Description',
                'help' => '',
            ),
            'tags' => array(
                'name' => 'Tags',
                'help' => 'Mapping only supports one tag. Multiple words will become a single tag.',
            ),
            'milestoneid' => array(
                'name' => 'Milestone',
                'help' => '',
            ),
            'planHours' => array(
                'name' => 'Planned hours',
                'help' => 'Mapping this will also set "Hours left" as the same value to save you time.',
            ),
            'dateToFinish' => array(
                'name' => 'Due Date',
                'help' => '',
            ),
            'editorId' => array(
                'name' => 'Assignee',
                'help' => '',
            ),
            'priority' => array(
                'name' => 'Priority',
                'help' => '',
            ),
        );
    }

    /**
     * Validated the mapped data. Eventually sets warnings and errors in session
     *
     * @param string $tempFilePath
     *
     * @param array  $dataToValidate
     *
     * @return array|bool
     *
     * @throws Exception
     */
    public function dataValidationCheck(string $tempFilePath, array $dataToValidate): array|bool
    {
        if (!$tempFilePath) {
            error_log('Temp file path is required.');
            throw new \Exception('Temp file path is required.');
        }

        $data = $dataToValidate['data'];
        $mapping_data = $dataToValidate['mapping_data'];
        $supportedFields = $this->getSupportedFields();
        $projectId = $dataToValidate['projectId'];

        $validationData['warnings'] = [];
        $validationData['errors'] = [];

        // Loop field mapping
        foreach ($mapping_data as $key => $mapping_datum) {
            // User defined keys can contain spaces,
            $key = str_replace('_', ' ', $key);

            // Loop data and match fields names
            foreach ($data as $data_key => &$datum) {
                if (trim($mapping_datum) === '-1') {
                    unset($datum[$key]);
                    if (empty($datum)) {
                        $validationData['warnings'][] = 'Ticket ' . $data_key . ': Was removed due to being empty.';
                        unset($data[$data_key]);
                    }
                    continue;
                }
                if (empty($datum[$key])) {
                    $validationData['warnings'][] = 'Ticket ' . $data_key . ': "' . $supportedFields[$mapping_datum]['name'] . '" empty and was removed.';

                    unset($datum[$key]);

                    if (empty($datum)) {
                        $validationData['warnings'][] = 'Ticket ' . $data_key . ': Was removed due to being empty.';
                        unset($data[$data_key]);
                        continue;
                    }
                }
                if ($mapping_datum === 'milestoneid') {
                    if (empty($datum[$key])) {
                        continue;
                    }

                    $exists = $this->checkMilestoneExist($datum[$key], $projectId, false);
                    if (!$exists) {
                        $validationData['errors']['Milestone'][$datum[$key]] = 'The following milestone does not exist: ';
                    }
                }
                if ($mapping_datum === 'dateToFinish') {
                    if (empty($datum[$key])) {
                        continue;
                    }
                    $dateFormat = $dataToValidate['dateFormat'];
                    $dateValid = $this->validateDate($datum[$key], $dateFormat);
                    if (!$dateValid) {
                        $validationData['errors']['DueDate'][$datum[$key]] = 'The following DueDate is not in the format ' . $dateFormat . ': ';
                    }
                }
                if ($mapping_datum === 'editorId') {
                    if (empty($datum[$key])) {
                        continue;
                    }
                    $assigneeEmailValid = $this->validateAssigneeEmail($datum[$key]);
                    if (!$assigneeEmailValid) {
                        $validationData['errors']['Assignee'][$datum[$key]] = 'The following Assignee email is not a valid email format: ';
                    }
                }
                if ($mapping_datum === 'priority') {
                    if (empty($datum[$key])) {
                        continue;
                    }
                    $priorityValid = $this->validatePriority($datum[$key]);
                    if (!$priorityValid) {
                        $validationData['errors']['Priority'][$datum[$key]] = 'The following priority is not valid: ';
                    }
                }
            }
        }

        $validationData['mapping_data'] = $mapping_data;
        $validationData['data'] = $data;

        $savedSuccess = $this->saveDataToTempFile($validationData, $tempFilePath);

        if (!$savedSuccess) {
            error_log('Failed to save temp file.');
            throw new \Exception('Failed to save temp file.');
        }
        return $this->getDataFromTempFile($tempFilePath);
    }

    /**
     * Validates a given date with a given format.
     *
     * @param string $date
     *
     * @param string $format
     *
     * @return bool
     */
    private function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    /**
     * getAllProjects from timesheet repository
     *
     * @return array<int<0, max>, array<string, mixed>>
     */
    public function getAllProjectIds(): array
    {
        $projectData = $this->projectService->getAll();
        $reducedData = [];
        foreach ($projectData as $projectDatum) {
            $reducedData[] = [
                'id' => $projectDatum['id'],
                'name' => $projectDatum['name'],
            ];
        }
        return $reducedData;
    }

    /**
     * Saves the data to a tmp file, either given or creates a new one.
     *
     * @param array  $dataToSave
     *
     * @param string $tempFilePath
     *
     * @return string|bool
     */
    public function saveDataToTempFile(array $dataToSave, string $tempFilePath = ''): string|bool
    {

        if ($tempFilePath === '') {
            // Create tmp file
            $csvDataFile = tempnam(sys_get_temp_dir(), 'csv_data_');

            // Encode data as JSON
            $dataToStore = json_encode($dataToSave);

            // Store data in the tmp file
            $savedSuccess = file_put_contents($csvDataFile, $dataToStore);

            if ($savedSuccess) {
                return $csvDataFile;
            }
        } else {
            // Get data from existing tmp file
            $csvDataEncoded = file_get_contents($tempFilePath);

            // Decode data from JSON
            $csvData = json_decode($csvDataEncoded, true);
            foreach ($dataToSave as $datumKey => $datumToSave) {
                $csvData[$datumKey] = $datumToSave;
            }

            // Re-encode data as JSON
            $dataToStore = json_encode(
                $csvData
            );

            // Store data in the tmp file
            file_put_contents($tempFilePath, $dataToStore);

            return $tempFilePath;
        }
        return false;
    }

    /**
     * Gets the data from a given temp file.
     *
     * @param string $tempFilePath
     *
     * @return array|bool
     *
     * @throws Exception
     */
    public function getDataFromTempFile(string $tempFilePath): array|bool
    {
        // Get data from existing tmp file
        $csvDataEncoded = file_get_contents($tempFilePath);

        if ($csvDataEncoded) {
            // Decode data from JSON
            $csvData = json_decode($csvDataEncoded, true);

            return $csvData ?? false;
        } else {
            error_log('Cannot read data from tmp file');
            throw new Exception('Cannot read data from tmp file');
        }
    }

    /**
     * Validates the assignee email address.
     *
     * @param string $assigneeEmail The email address to validate.
     *
     * @return bool True if the email address is valid, false otherwise.
     */
    private function validateAssigneeEmail(string $assigneeEmail): bool
    {
        return $assigneeEmail === filter_var($assigneeEmail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validates the priority value against predefined priorities.
     *
     * @param string $priority The priority value to validate.
     * @return bool Returns true if the priority value is valid, false otherwise.
     */
    public function validatePriority(string $priority): bool
    {
        // Valid priorities are defined in app/Domain/Tickets/Repositories/Tickets.php:97
        $validPriorities = array('1', '2', '3', '4', '5');

        return in_array($priority, $validPriorities);
    }
}
