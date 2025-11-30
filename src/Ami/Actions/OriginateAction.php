<?php

namespace yousefkadah\FreePbx\Ami\Actions;

use yousefkadah\FreePbx\Ami\AmiConnection;

class OriginateAction
{
    protected AmiConnection $connection;

    public function __construct(AmiConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Originate a call (click-to-call).
     *
     * @param string $fromExtension The extension to call first
     * @param string $toNumber The number to call after the extension answers
     * @param array $options Additional options
     */
    public function call(string $fromExtension, string $toNumber, array $options = []): \React\Promise\PromiseInterface
    {
        $context = $options['context'] ?? config('freepbx.click_to_call.context', 'from-internal');
        $priority = $options['priority'] ?? config('freepbx.click_to_call.priority', 1);
        $timeout = $options['timeout'] ?? config('freepbx.click_to_call.timeout', 30000);
        $callerId = $options['caller_id'] ?? config('freepbx.click_to_call.caller_id', $fromExtension);

        $action = [
            'Action' => 'Originate',
            'Channel' => "Local/{$fromExtension}@{$context}",
            'Exten' => $toNumber,
            'Context' => $context,
            'Priority' => $priority,
            'Timeout' => $timeout,
            'CallerID' => $callerId,
            'Async' => 'true',
        ];

        // Add custom variables if provided
        if (isset($options['variables'])) {
            foreach ($options['variables'] as $key => $value) {
                $action["Variable"] = "{$key}={$value}";
            }
        }

        return $this->connection->sendAction($action);
    }

    /**
     * Originate a call to a specific channel.
     */
    public function callChannel(string $channel, string $application, string $data, array $options = []): \React\Promise\PromiseInterface
    {
        $timeout = $options['timeout'] ?? config('freepbx.click_to_call.timeout', 30000);
        $callerId = $options['caller_id'] ?? '';

        $action = [
            'Action' => 'Originate',
            'Channel' => $channel,
            'Application' => $application,
            'Data' => $data,
            'Timeout' => $timeout,
            'CallerID' => $callerId,
            'Async' => 'true',
        ];

        return $this->connection->sendAction($action);
    }
}
