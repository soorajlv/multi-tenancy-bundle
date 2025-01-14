<?php

declare(strict_types=1);

namespace MultiTenancyBundle\Doctrine\Database;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

class EntityManagerFactory
{
    public function create(array $conn, Configuration $configuration): EntityManager
    {
        $connection = DriverManager::getConnection($conn, $configuration);
        return new EntityManager($connection, $configuration);
    }
}
