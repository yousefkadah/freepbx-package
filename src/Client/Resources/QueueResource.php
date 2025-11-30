<?php

namespace Yousef\FreePbx\Client\Resources;

class QueueResource extends Resource
{
    /**
     * List all queues.
     */
    public function list(): array
    {
        return $this->client->get('queues') ?? [];
    }

    /**
     * Get a specific queue.
     */
    public function get(string $queueId): ?array
    {
        return $this->client->get("queues/{$queueId}");
    }

    /**
     * Create a new queue.
     */
    public function create(array $data): array
    {
        return $this->client->post('queues', $data);
    }

    /**
     * Update a queue.
     */
    public function update(string $queueId, array $data): array
    {
        return $this->client->put("queues/{$queueId}", $data);
    }

    /**
     * Delete a queue.
     */
    public function delete(string $queueId): bool
    {
        $this->client->delete("queues/{$queueId}");
        return true;
    }

    /**
     * Add an agent to a queue.
     */
    public function addAgent(string $queueId, string $extension, array $options = []): array
    {
        return $this->client->post("queues/{$queueId}/agents", array_merge([
            'extension' => $extension,
        ], $options));
    }

    /**
     * Remove an agent from a queue.
     */
    public function removeAgent(string $queueId, string $extension): bool
    {
        $this->client->delete("queues/{$queueId}/agents/{$extension}");
        return true;
    }

    /**
     * Get queue statistics.
     */
    public function stats(string $queueId): ?array
    {
        return $this->client->get("queues/{$queueId}/stats");
    }

    /**
     * Get all agents in a queue.
     */
    public function agents(string $queueId): array
    {
        return $this->client->get("queues/{$queueId}/agents") ?? [];
    }

    /**
     * Pause an agent in a queue.
     */
    public function pauseAgent(string $queueId, string $extension, ?string $reason = null): array
    {
        return $this->client->post("queues/{$queueId}/agents/{$extension}/pause", [
            'reason' => $reason,
        ]);
    }

    /**
     * Unpause an agent in a queue.
     */
    public function unpauseAgent(string $queueId, string $extension): array
    {
        return $this->client->post("queues/{$queueId}/agents/{$extension}/unpause");
    }
}
