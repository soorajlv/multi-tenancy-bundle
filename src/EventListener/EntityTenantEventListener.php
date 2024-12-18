<?php

declare(strict_types=1);

namespace MultiTenancyBundle\EventListener;

use MultiTenancyBundle\Doctrine\Database\CreateTenantInterface;
use MultiTenancyBundle\Doctrine\Database\RemoveTenantInterface;
use MultiTenancyBundle\Entity\Tenant;

final readonly class EntityTenantEventListener
{
    public function __construct(private CreateTenantInterface $tenantCreateDatabase, private RemoveTenantInterface $tenantRemoveDatabase)
    {
    }

    /**
     * After persist a new tenant, this create the schema on the database
     *
     * @param Tenant $args
     * @return void
     */
    public function postPersist(Tenant $args): void
    {
        $this->tenantCreateDatabase->create($args->getUuid(), $args->getId());
    }

    /**
     * Pre remove a tenant, this remove the schema on the database
     *
     * @param Tenant $args
     * @return void
     */
    public function preRemove(Tenant $args)
    {
        $this->tenantRemoveDatabase->remove($args->getUuid(), $args->getId());
    }
}
