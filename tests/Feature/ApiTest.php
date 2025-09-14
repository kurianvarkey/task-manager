<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use Tests\TestCase;

final class ApiTest extends TestCase
{
    /**
     * Test the application returns a successful response.
     */
    public function test_application_returns_a_successful_response(): void
    {
        $this->get('/api')
            ->assertStatus(Response::HTTP_OK);
    }

    /**
     * Test the api not exists.
     */
    public function test_api_endpoint_not_exists(): void
    {
        $this->get('/api/notexists')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_api_unauthenticated(): void
    {
        $this->get('/api/tags')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
