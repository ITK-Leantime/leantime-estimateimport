<?php

namespace Leantime\Plugins\EstimateImport\Services;

use DateTime;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * ImportHelper class
 */
class ImportHelper
{
    private TicketService $ticketService;
    private $projectMilestones;

    /**
     * constructor
     *
     * @param TicketService $ticketService
     * @return void
     */
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Get milestone object internally
     *
     * @return array
     *
     */
    private function getProjectMilestones(bool $update = false): array
    {
        if (!isset($this->projectMilestones) || $update) {
            $this->setProjectMilestones();
        }
        return $this->projectMilestones;
    }

    /**
     * Set milestone object internally
     *
     * @return void
     *
     */
    private function setProjectMilestones(): void
    {
        $milestoneData = $this->ticketService->getAllMilestones([
            'type' => 'milestone',
            'currentProject' => $_SESSION['csv_data']['project_id'], // Project id is set in importSettings
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
     * @return array
     *
     */
    public function checkMilestoneExist($milestone, bool $update = false): bool|string
    {
        $milestones = $this->getProjectMilestones($update);
        return array_reduce($milestones, function ($carry, $item) use ($milestone) {
            return $carry !== false ? $carry : ($item['headline'] === $milestone ? $item['id'] : false);
        }, false);
    }

     /**
     * Adds a given milestone
     *
     * @return array
     *
     */
    public function addMilestone($milestone): array|bool|int
    {
        $params = array(
            'headline' => $milestone,
        );
        return $this->ticketService->quickAddMilestone($params);
    }

/**
     * Gets all supported fields for mapping
     *
     * @return array
     *
     */
    public function getSupportedFields(): array
    {
        $supportedFields = array(
            'headline' => array(
                'name' => 'Titel',
                'help' => '',
            ),
            'description' => array(
                'name' => 'Beskrivelse',
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
                'name' => 'Planlagte timer',
                'help' => 'Mapping this will also set "Hours left" as the same value to save you time.',
            ),
            'dateToFinish' => array(
                'name' => 'Due Date',
                'help' => '',
            ),
        );


        return $supportedFields;
    }

    /**
     * Validated the mapped data. Eventually sets warnings and errors
     *
     * @return void
     *
     */
    public function dataValidationCheck(): void
    {
        unset($_SESSION['csv_data']['warnings']);
        unset($_SESSION['csv_data']['errors']);

        $data = $_SESSION['csv_data']['data'];
        $params = $_SESSION['csv_data']['mapping_data'];

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
                    $exists = $this->checkMilestoneExist($datum[$key], true);
                    if (!$exists) {
                        $_SESSION['csv_data']['errors']['Milestone'][$datum[$key]] = 'The following milestone does not exist: ';
                    }
                }
                if ($param === 'dateToFinish') {
                    if (empty($datum[$key])) {
                        continue;
                    }
                    $dateFormat = $_SESSION['csv_data']['date_format'];
                    $dateValid = $this->validateDate($datum[$key], $dateFormat);
                    if (!$dateValid) {
                        $_SESSION['csv_data']['errors']['DueDate'][$datum[$key]] = 'The following DueDate is not in the format ' . $dateFormat . ': ';
                    }
                }
            }
        }
        $_SESSION['csv_data']['mappings'] = $params;
        $_SESSION['csv_data']['result'] = $data;
    }

    /**
     * Validates a given date with a given format.
     *
     * @return bool
     *
     */
    private function validateDate($date, $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}
