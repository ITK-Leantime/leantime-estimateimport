<?php

namespace Leantime\Plugins\EstimateImport\Services;

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
    private function getProjectMilestones(): array
    {
        if (!isset($this->projectMilestones)) {
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
    public function checkMilestoneExist($milestone): bool|string
    {
        $milestones = $this->getProjectMilestones();

        return array_reduce($milestones, function ($carry, $item) use ($milestone) {
            return ($item['headline'] === $milestone) ? $item['id'] : false;
        }, false);
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
        );


        return $supportedFields;
    }
}
