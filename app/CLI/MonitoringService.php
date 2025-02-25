<?php

namespace App\CLI;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Cache\CacheInterface;

/**
 * Asynchronous monitoring service for the roller coaster system
 * * Implements real-time statistics display without blocking loops.
 */
class MonitoringService
{
    /**
     * @var LoopInterface
     */
    private LoopInterface $loop;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var string
     */
    private string $logFile;

    /**
     * @var string
     */
    private string $environment;

    /**
     * @var string
     */
    private string $lastChecksum = '';

    /**
     * @var int
     */
    private int $refreshInterval = 5;

    /**
     * @param CacheInterface|null $cache
     * @param string $environment
     */
    public function __construct(CacheInterface $cache = null, string $environment = 'prod')
    {
        $this->cache = $cache ?? service('cache');
        $this->environment = $environment;
        $this->logFile = WRITEPATH . 'logs/monitoring_' . date('Y-m-d') . '.log';
    }

    public function run(): void
    {
        CLI::write("🚀 Uruchamianie monitora systemu kolejek górskich...", 'green');

        $this->loop = Factory::create();

        $this->loop->addPeriodicTimer($this->refreshInterval, function () {
            $this->displayStatistics();
        });

        $this->displayStatistics();

        CLI::write('📊 Monitoring uruchomiony. Naciśnij Ctrl+C aby zatrzymać.', 'green');

        $this->loop->run();
    }

    private function displayStatistics(): void
    {
        try {
            $statistics = $this->getStatisticsFromCache();

            $currentChecksum = md5(json_encode($statistics));
            if ($currentChecksum === $this->lastChecksum) {
                return;
            }

            $this->lastChecksum = $currentChecksum;

            $this->renderStatistics($statistics);
        } catch (\Exception $e) {
            CLI::error("❌ Błąd pobierania statystyk: {$e->getMessage()}");
            CLI::error($e->getTraceAsString());
        }
    }

    /**
     * @param array $statistics
     */
    private function renderStatistics(array $statistics): void
    {
        CLI::clearScreen();

        CLI::write('📊 [Godzina ' . date('H:i') . ']', 'yellow');
        CLI::newLine();

        CLI::write("🚀 Kolejki górskie - Status", 'cyan');
        CLI::write(str_repeat('=', 40), 'cyan');

        foreach ($statistics['coasters'] as $coaster) {
            $this->renderCoaster($coaster);
        }

        $this->renderSummary($statistics);
    }

    /**
     * @param object $coaster
     */
    private function renderCoaster(object $coaster): void
    {
        CLI::write("🎢 Kolejka: {$coaster->id}", 'light_blue');
        CLI::write("  🕒 Godziny: {$coaster->operatingHoursFrom} - {$coaster->operatingHoursTo}");
        CLI::write("  🚋 Wagony:  {$coaster->currentWagonCount}");
        CLI::write("  👥 Personel: {$coaster->staffCount}/{$coaster->requiredStaff}");
        CLI::write("  🎟️ Klienci dziennie: {$coaster->dailyClients}");

        if ($coaster->status === 'OK') {
            CLI::write("  ✅ Status: OK", 'green');
        } else {
            CLI::write("  ❌ Status: Problem", 'red');

            foreach ($coaster->issues as $issue) {
                CLI::write("     - 🔴 {$issue}", 'light_red');
                $this->logProblem($coaster->id, $issue);
            }
        }

        CLI::write(str_repeat('-', 40), 'white');
    }

    /**
     * @param array $statistics
     */
    private function renderSummary(array $statistics): void
    {
        CLI::write("📊 Podsumowanie:", 'yellow');
        CLI::write(str_repeat('=', 40), 'yellow');
        CLI::write("🎢 Kolejek: " . str_pad($statistics['totalCoasters'], 5, ' ', STR_PAD_LEFT));
        CLI::write("🚋 Wagonów: " . str_pad($statistics['totalWagons'], 5, ' ', STR_PAD_LEFT));
        CLI::write("👥 Personel: " . str_pad($statistics['totalStaff'], 5, ' ', STR_PAD_LEFT) .
            " (Wymagany: {$statistics['requiredStaff']})");
        CLI::write("🎟️ Klientów dziennie: " . str_pad($statistics['totalDailyClients'], 5, ' ', STR_PAD_LEFT));
        CLI::write("🏠 Pojemność systemu: " . str_pad($statistics['totalClientCapacity'], 5, ' ', STR_PAD_LEFT));

        if ($statistics['staffBalance'] < 0) {
            CLI::write("⚠️ Braki kadrowe: " . abs($statistics['staffBalance']), 'red');
        } elseif ($statistics['staffBalance'] > 0) {
            CLI::write("🟡 Nadwyżka personelu: {$statistics['staffBalance']}", 'yellow');
        }

        if ($statistics['capacityBalance'] < 0) {
            CLI::write("⚠️ Braki pojemności: " . abs($statistics['capacityBalance']) . " klientów", 'red');
        } elseif ($statistics['capacityBalance'] > $statistics['totalDailyClients']) {
            CLI::write("🟡 Nadwyżka pojemności: {$statistics['capacityBalance']} klientów", 'yellow');
        }
    }

    /**
     * @return array
     */
    private function getStatisticsFromCache(): array
    {
        $prefix = $this->environment . '_coasters_';
        $coasterIds = $this->cache->get($prefix . 'index') ?? [];

        $coasters = [];
        $totalWagons = 0;
        $totalStaff = 0;
        $requiredStaff = 0;
        $totalDailyClients = 0;
        $totalClientCapacity = 0;

        foreach ($coasterIds as $id) {
            $coasterData = $this->getCoasterData($id, $prefix);
            if (!$coasterData) {
                continue;
            }

            $totalWagons += $coasterData->currentWagonCount;
            $totalStaff += $coasterData->staffCount;
            $requiredStaff += $coasterData->requiredStaff;
            $totalDailyClients += $coasterData->dailyClients;
            $totalClientCapacity += $coasterData->clientCapacity;

            $coasters[] = $coasterData;
        }

        return [
            'totalCoasters' => count($coasters),
            'totalWagons' => $totalWagons,
            'totalStaff' => $totalStaff,
            'requiredStaff' => $requiredStaff,
            'totalDailyClients' => $totalDailyClients,
            'totalClientCapacity' => $totalClientCapacity,
            'coasters' => $coasters,
            'staffBalance' => $totalStaff - $requiredStaff,
            'capacityBalance' => $totalClientCapacity - $totalDailyClients
        ];
    }

    /**
     * @param string $id
     * @param string $prefix
     * @return object|null
     */
    private function getCoasterData(string $id, string $prefix): ?object
    {
        $data = $this->cache->get($prefix . $id);
        if (!$data) {
            return null;
        }

        $coaster = new \stdClass();
        $coaster->id = $data['id'];
        $coaster->staffCount = $data['staffCount'] ?? 0;
        $coaster->dailyClients = $data['dailyClients'] ?? 0;
        $coaster->trackLength = $data['trackLength'] ?? 0;
        $coaster->operatingHoursFrom = $data['operatingHoursFrom'] ?? '00:00';
        $coaster->operatingHoursTo = $data['operatingHoursTo'] ?? '00:00';

        $wagons = $this->getWagonsForCoaster($id);
        $coaster->wagons = $wagons;
        $coaster->currentWagonCount = count($wagons);
        $coaster->requiredStaff = 1 + ($coaster->currentWagonCount * 2); // 1 for coaster + 2 for wagon

        $coaster->clientCapacity = $this->calculateCapacity($coaster, $wagons);

        $this->determineCoasterStatus($coaster);

        return $coaster;
    }

    /**
     * @param string $coasterId
     * @return array
     */
    private function getWagonsForCoaster(string $coasterId): array
    {
        $wagonPrefix = $this->environment . '_coaster_wagons_';
        $wagonIds = $this->cache->get($wagonPrefix . $coasterId) ?? [];
        $wagons = [];

        foreach ($wagonIds as $wagonId) {
            $wagonData = $this->cache->get($this->environment . '_wagons_' . $wagonId);
            if ($wagonData) {
                $wagons[] = $wagonData;
            }
        }

        return $wagons;
    }

    /**
     * @param object $coaster
     */
    private function determineCoasterStatus(object $coaster): void
    {
        $coaster->status = 'OK';
        $coaster->issues = [];

        if ($coaster->staffCount < $coaster->requiredStaff) {
            $coaster->status = 'Problem';
            $coaster->issues[] = 'Brakuje ' . ($coaster->requiredStaff - $coaster->staffCount) . ' pracowników';
        }

        if ($coaster->clientCapacity < $coaster->dailyClients) {
            $coaster->status = 'Problem';
            $wagonsNeeded = $this->calculateRequiredWagons($coaster);
            $coaster->issues[] = 'Brak ' . $wagonsNeeded . ' wagonów do obsługi wszystkich klientów';
        }
    }

    /**
     * @param object $coaster
     * @return int
     */
    private function calculateRequiredWagons(object $coaster): int
    {
        if ($coaster->clientCapacity >= $coaster->dailyClients) {
            return 0;
        }

        $capacityPerWagon = 100;
        if ($coaster->currentWagonCount > 0) {
            $capacityPerWagon = $coaster->clientCapacity / $coaster->currentWagonCount;
        }

        $additionalCapacityNeeded = $coaster->dailyClients - $coaster->clientCapacity;
        return ceil($additionalCapacityNeeded / $capacityPerWagon);
    }

    /**
     * @param object $coaster
     * @param array $wagons
     * @return int
     */
    private function calculateCapacity(object $coaster, array $wagons): int
    {
        if (empty($wagons)) {
            return 0;
        }

        $avgSeats = $this->calculateAverageSeats($wagons);
        $avgSpeed = $this->calculateAverageSpeed($wagons);

        $rideTimeMinutes = ceil($coaster->trackLength / $avgSpeed / 60);

        $operatingMinutes = $this->calculateOperatingMinutes($coaster);

        $cycleTime = $rideTimeMinutes + 5;
        $ridesPerWagon = floor($operatingMinutes / $cycleTime);

        return $avgSeats * count($wagons) * $ridesPerWagon;
    }

    /**
     * @param array $wagons
     * @return float
     */
    private function calculateAverageSeats(array $wagons): float
    {
        $totalSeats = 0;
        foreach ($wagons as $wagon) {
            $totalSeats += $wagon['seatCount'] ?? 0;
        }
        return $totalSeats / count($wagons);
    }

    /**
     * @param array $wagons
     * @return float
     */
    private function calculateAverageSpeed(array $wagons): float
    {
        $totalSpeed = 0;
        foreach ($wagons as $wagon) {
            $totalSpeed += $wagon['speed'] ?? 1.0;
        }
        return $totalSpeed / count($wagons);
    }

    /**
     * @param object $coaster
     * @return int
     */
    private function calculateOperatingMinutes(object $coaster): int
    {
        $fromParts = explode(':', $coaster->operatingHoursFrom);
        $toParts = explode(':', $coaster->operatingHoursTo);

        $fromMinutes = (int)$fromParts[0] * 60 + (int)($fromParts[1] ?? 0);
        $toMinutes = (int)$toParts[0] * 60 + (int)($toParts[1] ?? 0);

        return $toMinutes - $fromMinutes;
    }

    /**
     * @param string $coasterId
     * @param string $issue
     */
    private function logProblem(string $coasterId, string $issue): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Kolejka {$coasterId} - Problem: {$issue}" . PHP_EOL;

        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
