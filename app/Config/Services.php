<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Application\Handlers\RegisterCoasterHandler;
use App\Application\Handlers\RegisterWagonHandler;
use App\Application\Handlers\RemoveWagonHandler;
use App\Application\Handlers\UpdateCoasterHandler;
use App\Application\Queries\GetCoasterDetailsHandler;
use App\Application\Queries\GetSystemStatisticsHandler;
use App\Infrastructure\Persistence\RedisCoasterRepository;
use App\Infrastructure\Persistence\RedisWagonRepository;
use App\Infrastructure\Services\EventDispatcher;
use App\Infrastructure\Services\LoggingService;
use App\Infrastructure\Services\UuidGenerator;

class Services extends BaseService
{
    public static function registerCoasterHandler($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('registerCoasterHandler');
        }

        $environment = getenv('APP_ENV') ?: 'prod';
        $redisPrefix = ($environment === 'dev') ? 'dev' : 'prod';

        $idGenerator = new UuidGenerator();
        $eventDispatcher = new EventDispatcher(service('logger'));

        $wagonRepository = new RedisWagonRepository(service('cache'), $redisPrefix);
        $coasterRepository = new RedisCoasterRepository(service('cache'), $wagonRepository, $redisPrefix);

        return new RegisterCoasterHandler($coasterRepository, $eventDispatcher, $idGenerator);
    }

    public static function registerWagonHandler($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('registerWagonHandler');
        }

        $environment = getenv('APP_ENV') ?: 'prod';
        $redisPrefix = ($environment === 'dev') ? 'dev' : 'prod';

        $idGenerator = new UuidGenerator();
        $eventDispatcher = new EventDispatcher(service('logger'));

        $wagonRepository = new RedisWagonRepository(service('cache'), $redisPrefix);
        $coasterRepository = new RedisCoasterRepository(service('cache'), $wagonRepository, $redisPrefix);

        return new RegisterWagonHandler($coasterRepository, $wagonRepository, $eventDispatcher, $idGenerator);
    }

    public static function removeWagonHandler($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('removeWagonHandler');
        }

        $environment = getenv('APP_ENV') ?: 'prod';
        $redisPrefix = ($environment === 'dev') ? 'dev' : 'prod';

        $eventDispatcher = new EventDispatcher(service('logger'));

        $wagonRepository = new RedisWagonRepository(service('cache'), $redisPrefix);
        $coasterRepository = new RedisCoasterRepository(service('cache'), $wagonRepository, $redisPrefix);

        return new RemoveWagonHandler($coasterRepository, $wagonRepository, $eventDispatcher);
    }

    public static function updateCoasterHandler($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('updateCoasterHandler');
        }

        $environment = getenv('APP_ENV') ?: 'prod';
        $redisPrefix = ($environment === 'dev') ? 'dev' : 'prod';

        $eventDispatcher = new EventDispatcher(service('logger'));

        $wagonRepository = new RedisWagonRepository(service('cache'), $redisPrefix);
        $coasterRepository = new RedisCoasterRepository(service('cache'), $wagonRepository, $redisPrefix);

        return new UpdateCoasterHandler($coasterRepository, $eventDispatcher);
    }

    public static function getCoasterDetailsHandler($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('getCoasterDetailsHandler');
        }

        $environment = getenv('APP_ENV') ?: 'prod';
        $redisPrefix = ($environment === 'dev') ? 'dev' : 'prod';

        $wagonRepository = new RedisWagonRepository(service('cache'), $redisPrefix);
        $coasterRepository = new RedisCoasterRepository(service('cache'), $wagonRepository, $redisPrefix);

        return new GetCoasterDetailsHandler($coasterRepository);
    }

    public static function getSystemStatisticsHandler($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('getSystemStatisticsHandler');
        }

        $environment = getenv('APP_ENV') ?: 'prod';
        $redisPrefix = ($environment === 'dev') ? 'dev' : 'prod';

        $wagonRepository = new RedisWagonRepository(service('cache'), $redisPrefix);
        $coasterRepository = new RedisCoasterRepository(service('cache'), $wagonRepository, $redisPrefix);

        return new GetSystemStatisticsHandler($coasterRepository);
    }
}
