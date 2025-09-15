<?php

namespace Tests\Feature\Traits;

use App\Models\User;

trait Common
{
    /**
     * Api key.
     */
    public string $apiKey;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = str()->random(60);
        User::factory(['api_key' => $this->apiKey])->create();
    }

    /**
     * Post json with header.
     */
    public function postWithHeader(string $endPoint, array $data)
    {
        return $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        )
            ->postJson($endPoint, $data);
    }

    /**
     * Get json with header.
     */
    public function getWithHeader(string $endPoint, array $data = [])
    {
        if (! empty($data)) {
            $endPoint .= '?' . http_build_query($data);
        }

        return $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        )
            ->getJson($endPoint);
    }

    /**
     * Put json with header.
     */
    public function putWithHeader(string $endPoint, array $data)
    {
        return $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        )
            ->putJson($endPoint, $data);
    }

    /**
     * Patch json with header.
     */
    public function patchWithHeader(string $endPoint, array $data)
    {
        return $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        )
            ->patchJson($endPoint, $data);
    }

    /**
     * Delete json with header.
     */
    public function deleteWithHeader(string $endPoint)
    {
        return $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ]
        )
            ->deleteJson($endPoint);
    }
}
