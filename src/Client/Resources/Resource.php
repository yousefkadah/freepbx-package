<?php

namespace yousefkadah\FreePbx\Client\Resources;

use yousefkadah\FreePbx\Client\FreePbxClient;

abstract class Resource
{
    protected FreePbxClient $client;

    public function __construct(FreePbxClient $client)
    {
        $this->client = $client;
    }
}
