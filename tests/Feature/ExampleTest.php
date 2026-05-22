<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_to_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
