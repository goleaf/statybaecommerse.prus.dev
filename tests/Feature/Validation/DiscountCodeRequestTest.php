<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use App\Models\DiscountCode;
use Tests\TestCase;

final class DiscountCodeRequestTest extends TestCase
{
    public function test_apply_requires_code_returns_422(): void
    {
        $this->postJson('/discount-codes/apply', [])
            ->assertStatus(422);
    }

    public function test_remove_requires_code_returns_422(): void
    {
        $this->postJson('/discount-codes/remove', [])
            ->assertStatus(422);
    }

    public function test_generate_document_requires_template_and_format_returns_422(): void
    {
        $code = DiscountCode::factory()->create();

        $this->postJson("/discount-codes/{$code->getKey()}/generate-document", [])
            ->assertStatus(422);
    }
}
