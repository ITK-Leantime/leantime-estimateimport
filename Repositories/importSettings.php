<?php

namespace Leantime\Plugins\EstimateImport\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;

/**
 * TimeTable Repository
 */
class ImportSettings
{
    /**
   * constructor
   *
   * @access public
   *
   */
    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

  /**
   * getAllProjects from timesheet repository class
   *
   * @return array
   * @throws BindingResolutionException
   */
    public function getAllProjectIds(): array
    {
        $projectRepository = app()->make(ProjectRepository::class);
        $projectData = $projectRepository->getAll();
        $filteredData = [];
        foreach ($projectData as $projectDatum) {
            $filteredData[] = [
            'id' => $projectDatum['id'],
            'name' => $projectDatum['name'],
            ];
        }
        return $filteredData;
    }
}
