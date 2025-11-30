<?php

namespace Yousef\FreePbx\Client\Resources;

class ExtensionResource extends Resource
{
    /**
     * List all extensions.
     */
    public function list(): array
    {
        return $this->client->get('extensions') ?? [];
    }

    /**
     * Get a specific extension.
     */
    public function get(string $extension): ?array
    {
        return $this->client->get("extensions/{$extension}");
    }

    /**
     * Create a new extension.
     */
    public function create(array $data): array
    {
        return $this->client->post('extensions', $data);
    }

    /**
     * Update an extension.
     */
    public function update(string $extension, array $data): array
    {
        return $this->client->put("extensions/{$extension}", $data);
    }

    /**
     * Delete an extension.
     */
    public function delete(string $extension): bool
    {
        $this->client->delete("extensions/{$extension}");
        return true;
    }

    /**
     * Get extension status.
     */
    public function status(string $extension): ?array
    {
        return $this->client->get("extensions/{$extension}/status");
    }

    /**
     * Bulk create extensions.
     */
    public function bulkCreate(array $extensions): array
    {
        $results = [];

        foreach ($extensions as $extension) {
            try {
                $results[] = $this->create($extension);
            } catch (\Exception $e) {
                $results[] = ['error' => $e->getMessage(), 'data' => $extension];
            }
        }

        return $results;
    }

    /**
     * Bulk delete extensions.
     */
    public function bulkDelete(array $extensionNumbers): array
    {
        $results = [];

        foreach ($extensionNumbers as $extension) {
            try {
                $this->delete($extension);
                $results[$extension] = true;
            } catch (\Exception $e) {
                $results[$extension] = false;
            }
        }

        return $results;
    }
}
