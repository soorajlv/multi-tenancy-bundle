<?php

declare(strict_types=1);

namespace MultiTenancyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class RemoveTenantEvent extends Event
{
    public function __construct(protected string $dbName, protected int $tenantId)
    {
    }

    public function dbName(): string
    {
        return $this->dbName;
    }

    public function tenantId(): int
    {
        return $this->tenantId;
    }
}