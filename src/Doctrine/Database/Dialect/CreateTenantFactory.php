<?php

declare(strict_types=1);

namespace MultiTenancyBundle\Doctrine\Database\Dialect;

use MultiTenancyBundle\Doctrine\Database\CreateTenantInterface;
use MultiTenancyBundle\Doctrine\Database\Dialect\MySql\CreateTenantMySql;
use MultiTenancyBundle\Doctrine\Database\Dialect\PostgreSql\CreateTenantPsql;
use MultiTenancyBundle\Doctrine\DBAL\TenantConnectionInterface;
use RuntimeException;

class CreateTenantFactory
{
    /**
     * @var CreateTenantMySql
     */
    private $createTenantMySql;

    /**
     * @var CreateTenantPsql
     */
    private $createTenantPsql;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setCreateTenantMySql(CreateTenantMySql $createTenantMySql)
    {
        $this->createTenantMySql = $createTenantMySql;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setCreateTenantPsql(CreateTenantPsql $createTenantPsql)
    {
        $this->createTenantPsql = $createTenantPsql;
    }

    public function __invoke(TenantConnectionInterface $tenantConnection): CreateTenantInterface
    {
        $service = match ($tenantConnection->getDriverConnection()) {
            Driver::MYSQL => $this->createTenantMySql,
            Driver::POSTGRESQL => $this->createTenantPsql,
            default => throw new RuntimeException('Invalid driver. Driver supported mysql and postgresql.'),
        };

        return $service;
    }
}