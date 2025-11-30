<?php

namespace Yousef\FreePbx\Client\Resources;

use Yousef\FreePbx\Client\FreePbxClient;

abstract class Resource
{
    protected FreePbxClient $client;

    public function __construct(FreePbxClient $client)
    {
        $this->client = $client;
    }
}
