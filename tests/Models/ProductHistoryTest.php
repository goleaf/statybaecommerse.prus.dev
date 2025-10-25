<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

final class ProductHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_configuration_includes_expected_fillable_and_casts(): void
    {
        // Instantiate the model so we can inspect the configuration arrays safely.
        $model = new ProductHistory();

        // Confirm the fillable properties match the whitelist defined in the model.
        self::assertSame([
            'product_id',
            'user_id',
            'action',
            'field_name',
            'old_value',
            'new_value',
            'description',
            'ip_address',
            'user_agent',
            'metadata',
            'causer_type',
            'causer_id',
            'created_at',
            'updated_at',
        ], $model->getFillable());

        // Verify that the cast definitions cover the complex payload columns.
        $casts = $model->getCasts();
        self::assertSame('array', $casts['metadata'] ?? null);
        self::assertSame('json', $casts['old_value'] ?? null);
        self::assertSame('json', $casts['new_value'] ?? null);
        self::assertSame('datetime', $casts['created_at'] ?? null);
        self::assertSame('datetime', $casts['updated_at'] ?? null);

        // Ensure the table name is fixed to avoid relying on Laravel's naming heuristics.
        self::assertSame('product_histories', $model->getTable());
    }

    public function test_relationships_return_expected_models(): void
    {
        // Create related models to associate with the history entry.
        $product = Product::factory()->create();
        $user = User::factory()->create();

        // Store a history record that references the product, user, and causer.
        $history = ProductHistory::factory()->for($product)->for($user)->create([
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
        ])->fresh();

        // The belongsTo relationships should resolve to the provided models.
        self::assertTrue($product->is($history->product));
        self::assertTrue($user->is($history->user));

        // The morph relationship should also resolve to the same user instance.
        self::assertInstanceOf(User::class, $history->causer);
        self::assertTrue($user->is($history->causer));
    }

    public function test_scopes_filter_records_by_common_constraints(): void
    {
        // Prepare multiple products and users to diversify the dataset.
        [$productA, $productB] = Product::factory()->count(2)->create();
        [$userA, $userB] = User::factory()->count(2)->create();

        // Create several history records with varying attributes for scope testing.
        $matching = ProductHistory::factory()->for($productA)->for($userA)->create([
            'action' => 'updated',
            'field_name' => 'price',
            'created_at' => now()->subDays(2),
        ]);
        $otherProduct = ProductHistory::factory()->for($productB)->for($userA)->create([
            'action' => 'created',
            'field_name' => 'name',
            'created_at' => now()->subDays(40),
        ]);
        $otherUser = ProductHistory::factory()->for($productA)->for($userB)->create([
            'action' => 'deleted',
            'field_name' => 'status',
            'created_at' => now()->subDays(10),
        ]);

        // The forProduct scope should target the desired product identifier.
        self::assertEqualsCanonicalizing([
            $matching->getKey(),
            $otherUser->getKey(),
        ], ProductHistory::query()->forProduct($productA->getKey())->pluck('id')->all());

        // The byUser scope should reduce the set to the provided user identifier.
        self::assertEqualsCanonicalizing([
            $matching->getKey(),
            $otherProduct->getKey(),
        ], ProductHistory::query()->byUser($userA->getKey())->pluck('id')->all());

        // The byAction scope should focus on the requested action string.
        self::assertSame([$matching->getKey()], ProductHistory::query()->byAction('updated')->pluck('id')->all());

        // The byField scope should isolate results using the specific field name.
        self::assertSame([$matching->getKey()], ProductHistory::query()->byField('price')->pluck('id')->all());

        // The recent scope should only return models created within the time boundary.
        Carbon::setTestNow(now());
        self::assertEqualsCanonicalizing([
            $matching->getKey(),
            $otherUser->getKey(),
        ], ProductHistory::query()->recent(30)->pluck('id')->all());
        Carbon::setTestNow();
    }

    public function test_accessors_format_values_and_generate_descriptions(): void
    {
        // Seed the translator with the strings used by the accessor methods.
        Lang::addLines([
            'admin.common.none' => 'None',
            'admin.common.yes' => 'Yes',
            'admin.common.no' => 'No',
            'admin.product_history.actions.created' => 'Created Action',
            'admin.product_history.actions.updated' => 'Updated Action',
            'admin.product_history.fields.name' => 'Name Field',
            'admin.product_history.summaries.created' => 'Created :field',
            'admin.product_history.summaries.deleted' => 'Deleted :field',
            'admin.product_history.summaries.updated' => ':field changed from :from to :to',
        ], 'en');

        // Create a history record with complex values to exercise the formatters.
        $history = ProductHistory::factory()->create([
            'action' => 'updated',
            'field_name' => 'name',
            'old_value' => ['foo' => 'bar'],
            'new_value' => true,
        ])->fresh();

        // Arrays should be encoded as JSON while preserving Unicode characters.
        $expectedJson = json_encode(['foo' => 'bar'], JSON_UNESCAPED_UNICODE);
        self::assertSame($expectedJson, $history->formatted_old_value);

        // Boolean values should be converted into localized strings.
        self::assertSame('Yes', $history->formatted_new_value);

        // The localized action and field labels should be resolved correctly.
        self::assertSame('Updated Action', $history->action_display);
        self::assertSame('Name Field', $history->field_display);

        // The summary should interpolate the formatted values within the translation string.
        self::assertSame("Name Field changed from {$expectedJson} to Yes", $history->change_summary);
    }

    public function test_create_history_entry_populates_metadata_and_request_context(): void
    {
        // Prepare the related models and bind a fake request so helpers can read context data.
        $product = Product::factory()->create(['sku' => 'SKU-123']);
        $user = User::factory()->create();
        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.5',
            'HTTP_USER_AGENT' => 'PHPUnit-Agent',
        ]);
        app()->instance('request', $request);

        // Persist a history entry using the convenience helper method.
        $history = ProductHistory::createHistoryEntry(
            $product,
            'updated',
            'price',
            10,
            15,
            'Price adjusted',
            $user
        );

        // Refresh the record to ensure database defaults are applied.
        $history->refresh();

        // The core attributes should reflect the provided parameters.
        self::assertSame($product->getKey(), $history->product_id);
        self::assertSame($user->getKey(), $history->user_id);
        self::assertSame('updated', $history->action);
        self::assertSame('price', $history->field_name);
        self::assertSame(10, $history->old_value);
        self::assertSame(15, $history->new_value);
        self::assertSame('Price adjusted', $history->description);

        // The request helper should populate the IP and user agent metadata.
        self::assertSame('203.0.113.5', $history->ip_address);
        self::assertSame('PHPUnit-Agent', $history->user_agent);

        // The metadata helper should enrich the payload with product details.
        self::assertSame($product->name, $history->metadata['product_name'] ?? null);
        self::assertSame('SKU-123', $history->metadata['product_sku'] ?? null);

        // Boot logic should set the causer fields to the provided user information.
        self::assertSame(User::class, $history->causer_type);
        self::assertSame($user->getKey(), $history->causer_id);
    }

    public function test_significant_change_helpers_detect_impact_levels(): void
    {
        // Create a history entry representing a price modification for impact analysis.
        $history = ProductHistory::factory()->create([
            'field_name' => 'price',
        ])->fresh();

        // Significant fields should be flagged by the helper.
        self::assertTrue($history->isSignificantChange());

        // Price changes should be reported as high impact adjustments.
        self::assertSame('high', $history->getChangeImpact());

        // Changing the field to a non-critical attribute should downgrade the impact.
        $history->field_name = 'description';
        self::assertFalse($history->isSignificantChange());
        self::assertSame('low', $history->getChangeImpact());
    }
}
