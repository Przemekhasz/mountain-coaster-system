<?php

namespace App\Controllers\Api;

use App\Application\Commands\RegisterWagonCommand;
use App\Application\Commands\RemoveWagonCommand;
use App\Application\Queries\GetCoasterDetailsQuery;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class WagonController extends ResourceController
{
    /**
     * Register a new wagon for a coaster
     */
    public function create($coasterId = null): ResponseInterface
    {
        try {
            if (empty($coasterId)) {
                return $this->failValidationErrors(['coasterId' => 'Coaster ID is required']);
            }

            $rules = [
                'ilosc_miejsc'    => 'required|integer|greater_than[0]',
                'predkosc_wagonu' => 'required|numeric|greater_than[0]',
            ];

            if (!$this->validate($rules)) {
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $query = new GetCoasterDetailsQuery($coasterId);
            $queryHandler = service('GetCoasterDetailsHandler');
            $coaster = $queryHandler->handle($query);

            if (!$coaster) {
                return $this->failNotFound('Roller coaster not found');
            }

            $data = $this->request->getJSON(true);

            $command = new RegisterWagonCommand(
                '', // ID will be generated
                $coasterId,
                $data['ilosc_miejsc'],
                $data['predkosc_wagonu']
            );

            $handler = service('RegisterWagonHandler');
            $wagonId = $handler->handle($command);

            $updatedCoaster = $queryHandler->handle($query);

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Wagon added successfully',
                'data' => [
                    'wagonId' => $wagonId,
                    'coaster' => $updatedCoaster
                ]
            ]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError('An error occurred while adding the wagon: ' . $e->getMessage());
        }
    }

    /**
     * Remove a wagon from a coaster
     */
    public function delete($coasterId = null, $wagonId = null): ResponseInterface
    {
        try {
            if (empty($coasterId)) {
                return $this->failValidationErrors(['coasterId' => 'Coaster ID is required']);
            }

            if (empty($wagonId)) {
                return $this->failValidationErrors(['wagonId' => 'Wagon ID is required']);
            }

            $command = new RemoveWagonCommand($coasterId, $wagonId);
            $handler = service('RemoveWagonHandler');
            $success = $handler->handle($command);

            if (!$success) {
                return $this->failNotFound('Wagon not found or not associated with this coaster');
            }

            $query = new GetCoasterDetailsQuery($coasterId);
            $queryHandler = service('GetCoasterDetailsHandler');
            $coaster = $queryHandler->handle($query);

            return $this->respond([
                'status' => 'success',
                'message' => 'Wagon removed successfully',
                'data' => $coaster
            ]);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            return $this->failServerError('An error occurred while removing the wagon: ' . $e->getMessage());
        }
    }
}
