<?php

declare(strict_types=1);

namespace Tests\Feature\Errors;

use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\Feature\TestCase;

final class ApiErrorFormatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->group(function (): void {
            Route::post('/testing/errors/validation', function (Request $request) {
                $request->validate([
                    'name' => ['required', 'string'],
                ]);

                return response()->json(['ok' => true]);
            });

            Route::get('/testing/errors/type/{id}', function (int $id) {
                return response()->json(['id' => $id]);
            });
        });
    }

    public function test_validation_exception_returns_structured_json(): void
    {
        $response = $this->postJson('/testing/errors/validation', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::VALIDATION_FAILED))
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonStructure([
                'title', 'status', 'detail', 'instance',
                'error' => ['code', 'context' => ['violations']],
                'correlation' => ['trace_id', 'correlation_id'],
                'meta' => ['locale', 'timestamp'],
            ]);
    }

    public function test_type_error_from_bad_route_param_returns_problem_json(): void
    {
        $response = $this->getJson('/testing/errors/type/not-a-number');

        $this->assertTrue(in_array($response->status(), [400, 422], true));

        $response
            ->assertJsonPath('type', ApiErrorResponse::typeFor(ErrorCodes::VALIDATION_FAILED))
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonStructure([
                'title', 'status', 'detail', 'instance',
                'error' => ['code'],
                'correlation' => ['trace_id', 'correlation_id'],
                'meta' => ['locale', 'timestamp'],
            ]);
    }
}

