<?php

namespace Yousef\FreePbx\Tenancy;

interface TenantResolverInterface
{
    /**
     * Resolve the current tenant ID.
     */
    public function resolve(): ?string;
}
