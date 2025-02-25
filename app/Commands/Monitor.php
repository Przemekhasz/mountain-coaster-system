<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\CLI\MonitoringService;

class Monitor extends BaseCommand
{
    /**
     * @var string
     */
    protected $group = 'RollerCoaster';

    /**
     * @var string
     */
    protected $name = 'monitor';

    /**
     * @var string
     */
    protected $description = 'Starts the asynchronous monitoring service for the roller coaster system.';

    /**
     * Przewodnik użycia komendy.
     *
     * @var string
     */
    protected $usage = 'monitor [options]';

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var array
     */
    protected $options = [
        '-d' => 'Run in development environment',
    ];

    /**
     * @param array $params
     * @return int
     */
    public function run(array $params): int
    {
        $environment = array_key_exists('d', $params) ? 'dev' : 'prod';

        try {
            $monitoringService = new MonitoringService(service('getSystemStatisticsHandler'), $environment);

            $monitoringService->run();
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
