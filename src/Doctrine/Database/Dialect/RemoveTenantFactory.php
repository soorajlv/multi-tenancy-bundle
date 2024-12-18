<?php

declare(strict_types=1);

namespace MultiTenancyBundle\Doctrine\Database\Dialect;

use MultiTenancyBundle\Doctrine\Database\Dialect\MySql\RemoveTenantMySql;
use MultiTenancyBundle\Doctrine\Database\Dialect\PostgreSql\RemoveTenantPsql;
use MultiTenancyBundle\Doctrine\Database\RemoveTenantInterface;
use MultiTenancyBundle\Doctrine\DBAL\TenantConnectionInterface;
use RuntimeException;

class RemoveTenantFactory
{
    /**
     * @var RemoveTenantMySql
     */
    private $removeTenantMySql;

    /**
     * @var RemoveTenantPsql
     */
    private $removeTenantPsql;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setRemoveTenantMySql(RemoveTenantMySql $removeTenantMySql)
    {
        $this->removeTenantMySql = $removeTenantMySql;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setRemoveTenantPsql(RemoveTenantPsql $removeTenantPsql)
    {
        $this->removeTenantPsql = $removeTenantPsql;
    }

    public function __invoke(TenantConnectionInterface $tenantConnection): RemoveTenantInterface
    {
        $service = match ($tenantConnection->getDriverConnection()) {
            Driver::MYSQL => $this->removeTenantMySql,
            Driver::POSTGRESQL => $this->removeTenantPsql,
            default => throw new RuntimeException('Invalid driver. Driver supported mysql and postgresql.'),
        };

        return $service;
    }
}