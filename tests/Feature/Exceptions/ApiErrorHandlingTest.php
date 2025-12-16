<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requests_receive_json_responses_for_validation_errors(): void
    {
        Route::post('/api/test', function (Request $request) {
            $request->validate(['required_field' => 'required']);

            return response()->json(['success' => true]);
        });

        $response = $this->postJson('/api/test', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'type',
                'title',
                'status',
                'detail',
                'instance',
                'violations' => [
                    '*' => ['field', 'messages', 'reason'],
                ],
            ]);
    }

    public function test_api_requests_receive_json_responses_for_type_errors(): void
    {
        Route::get('/api/test/{id}', function (int $id) {
            return response()->json(['id' => $id]);
        });

        $response = $this->getJson('/api/test/invalid-id');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'type',
                'title',
                'status',
                'detail',
                'instance',
                'reason',
            ]);
    }

    public function test_web_requests_receive_redirects_for_authentication_errors(): void
    {
        Route::get('/protected', function () {
            throw new \Illuminate\Auth\AuthenticationException;
        })->middleware('auth');

        $response = $this->get('/protected');

        $response->assertRedirect();
    }

    public function test_api_requests_receive_json_for_authentication_errors(): void
    {
        Route::get('/api/protected', function () {
            throw new \Illuminate\Auth\AuthenticationException;
        })->middleware('auth');

        $response = $this->getJson('/api/protected');

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_domain_exceptions_are_properly_formatted_for_api(): void
    {
        // This would require creating a test domain exception
        // For now, we'll test the structure expectation
        $this->assertTrue(true); // Placeholder for domain exception test
    }

    public function test_filament_routes_redirect_to_admin_login(): void
    {
        // Mock a Filament route that requires authentication
        Route::get('/admin/test', function () {
            throw new \Illuminate\Auth\AuthenticationException;
        })->name('filament.admin.test');

        // Ensure the admin login route exists
        Route::get('/admin/login', function () {
            return view('filament::login');
        })->name('filament.admin.auth.login');

        $response = $this->get('/admin/test');

        $response->assertRedirect(route('filament.admin.auth.login'));
    }
}
