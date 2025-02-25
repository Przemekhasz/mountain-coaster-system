<?php

namespace Config;

class Commands extends \CodeIgniter\Config\BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * List of Command Classes
     * --------------------------------------------------------------------------
     */
    public array $commands = [
        \App\Commands\Monitor::class,
    ];
}
