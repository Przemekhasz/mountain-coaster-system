<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    /**
     * Redis host.
     */
    public string|array|false $host = 'localhost';

    /**
     * Redis port.
     */
    public int $port = 6379;

    /**
     * Redis password.
     */
    public string|array|null|false $password = null;

    /**
     * Redis timeout.
     */
    public int $timeout = 0;

    /**
     * Redis database index.
     */
    public int $database = 0;

    public function __construct()
    {
        parent::__construct();

        if (getenv('REDIS_HOST')) {
            $this->host = getenv('REDIS_HOST');
        }

        if (getenv('REDIS_PORT')) {
            $this->port = (int) getenv('REDIS_PORT');
        }

        if (getenv('REDIS_PASSWORD')) {
            $this->password = getenv('REDIS_PASSWORD');
        }

        if (getenv('REDIS_TIMEOUT')) {
            $this->timeout = (int) getenv('REDIS_TIMEOUT');
        }

        if (getenv('REDIS_DATABASE')) {
            $this->database = (int) getenv('REDIS_DATABASE');
        }
    }
}
