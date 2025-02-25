<?php

namespace App\Controllers\Api;

use App\Application\Commands\RegisterCoasterCommand;
use App\Application\Commands\UpdateCoasterCommand;
use App\Application\Queries\GetCoasterDetailsQuery;
use App\Application\Queries\GetSystemStatisticsQuery;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class CoasterController extends ResourceController
{
    /**
     * Register a new roller coaster
     */
    public function create(): ResponseInterface
    {
        try {
            $rules = [
                'liczba_personelu' => 'required|integer|greater_than[0]',
                'liczba_klientow'  => 'required|integer|greater_than[0]',
                'dl_trasy'         => 'required|numeric|greater_than[0]',
                'godziny_od'       => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
                'godziny_do'       => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            ];

            if (!$this->validate($rules)) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $data = $this->request->getJSON(true);

            $command = new RegisterCoasterCommand(
                '', // ID will be generated
                $data['liczba_personelu'],
                $data['liczba_klientow'],
                $data['dl_trasy'],
                $data['godziny_od'],
                $data['godziny_do']
            );

            $handler = service('RegisterCoasterHandler');
            $coasterId = $handler->handle($command);

            $query = new GetCoasterDetailsQuery($coasterId);
            $queryHandler = service('GetCoasterDetailsHandler');
            $coaster = $queryHandler->handle($query);

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Roller coaster registered successfully',
                'data' => $coaster
            ]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError('An error occurred while registering the roller coaster: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing roller coaster
     */
    public function update($coasterId = null): ResponseInterface
    {
        try {
            if (empty($coasterId)) {
                return $this->failValidationErrors(['id' => 'Coaster ID is required']);
            }

            $rules = [
                'liczba_personelu' => 'required|integer|greater_than[0]',
                'liczba_klientow'  => 'required|integer|greater_than[0]',
                'godziny_od'       => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
                'godziny_do'       => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            ];

            if (!$this->validate($rules)) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $data = $this->request->getJSON(true);

            $command = new UpdateCoasterCommand(
                $coasterId,
                $data['liczba_personelu'],
                $data['liczba_klientow'],
                $data['godziny_od'],
                $data['godziny_do']
            );

            $handler = service('UpdateCoasterHandler');
            $success = $handler->handle($command);

            if (!$success) {
                return $this->failNotFound('Roller coaster not found');
            }

            $query = new GetCoasterDetailsQuery($coasterId);
            $queryHandler = service('GetCoasterDetailsHandler');
            $coaster = $queryHandler->handle($query);

            return $this->respond([
                'status' => 'success',
                'message' => 'Roller coaster updated successfully',
                'data' => $coaster
            ]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError('An error occurred while updating the roller coaster: ' . $e->getMessage());
        }
    }

    /**
     * Get a single roller coaster
     */
    public function show($coasterId = null): ResponseInterface
    {
        try {
            if (empty($coasterId)) {
                return $this->failValidationErrors(['id' => 'Coaster ID is required']);
            }

            $query = new GetCoasterDetailsQuery($coasterId);
            $handler = service('GetCoasterDetailsHandler');
            $coaster = $handler->handle($query);

            if (!$coaster) {
                return $this->failNotFound('Roller coaster not found');
            }

            return $this->respond([
                'status' => 'success',
                'data' => $coaster
            ]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError('An error occurred while retrieving the roller coaster: ' . $e->getMessage());
        }
    }

    /**
     * Get system statistics
     */
    public function statistics(): ResponseInterface
    {
        try {
            $query = new GetSystemStatisticsQuery();
            $handler = service('GetSystemStatisticsHandler');
            $statistics = $handler->handle($query);

            return $this->respond([
                'status' => 'success',
                'data' => $statistics
            ]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError('An error occurred while retrieving system statistics: ' . $e->getMessage());
        }
    }
}
