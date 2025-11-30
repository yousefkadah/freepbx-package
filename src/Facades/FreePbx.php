<?php

namespace Yousef\FreePbx\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Yousef\FreePbx\Client\Resources\ExtensionResource extensions()
 * @method static \Yousef\FreePbx\Client\Resources\QueueResource queues()
 * @method static mixed get(string $endpoint, array $params = [])
 * @method static mixed post(string $endpoint, array $data = [])
 * @method static mixed put(string $endpoint, array $data = [])
 * @method static mixed delete(string $endpoint)
 *
 * @see \Yousef\FreePbx\Client\FreePbxClient
 */
class FreePbx extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'freepbx';
    }
}
