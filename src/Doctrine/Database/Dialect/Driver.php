<?php

declare (strict_types=1);

namespace MultiTenancyBundle\Doctrine\Database\Dialect;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

class Driver
{
    public const MYSQL = 'mysql';
    public const POSTGRESQL = 'postgresql';

    public static function getDriverName(Connection $connection): string
    {
        return match(true) {  
            $connection->getDatabasePlatform() instanceof PostgreSQLPlatform => self::POSTGRESQL,
            $connection->getDatabasePlatform() instanceof MySQLPlatform => self::MYSQL,
            default => function(){
                throw new \Exception("Driver not supported");
            }
         };
    }

    public static function isPostgreSql(string $driver): bool
    {
        return self::POSTGRESQL === $driver;
    }
}