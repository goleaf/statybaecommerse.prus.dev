<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\NotificationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_template_configuration_is_explicit(): void
    {
        // Instantiate the model directly so we can make assertions about its metadata without touching the database.
        $model = new NotificationTemplate();

        // Guard mass-assignment by verifying the precise list of attributes allowed for bulk operations.
        self::assertSame([
            'name',
            'slug',
            'type',
            'event',
            'subject',
            'content',
            'variables',
            'is_active',
        ], $model->getFillable());

        // Ensure JSON columns are correctly cast to arrays alongside the boolean flag for the active state.
        self::assertSame([
            'subject' => 'array',
            'content' => 'array',
            'variables' => 'array',
            'is_active' => 'boolean',
        ], $model->getCasts());
    }

    public function test_localized_helpers_return_expected_strings(): void
    {
        // Persist a template with localized payloads to exercise the helper methods.
        $template = NotificationTemplate::query()->create([
            'name' => 'Order Confirmation',
            'slug' => 'order-confirmation',
            'type' => 'email',
            'event' => 'order_created',
            'subject' => ['en' => 'Order #:number', 'lt' => 'Užsakymas #:number'],
            'content' => ['en' => 'Hi {{name}}', 'lt' => 'Sveiki {{name}}'],
            'variables' => ['name', 'number'],
            'is_active' => true,
        ])->fresh();

        // The helpers should return the localized value and fall back to the fallback locale.
        self::assertSame('Order #:number', $template->getLocalizedSubject('en'));
        self::assertSame('Užsakymas #:number', $template->getLocalizedSubject('lt'));
        self::assertSame('Order #:number', $template->getLocalizedSubject('es'));

        self::assertSame('Hi {{name}}', $template->getLocalizedContent('en'));
        self::assertSame('Sveiki {{name}}', $template->getLocalizedContent('lt'));
        self::assertSame('Hi {{name}}', $template->getLocalizedContent('es'));
    }

    public function test_render_methods_perform_variable_replacement(): void
    {
        // Create a minimal template so we can assert how placeholders are expanded.
        $template = NotificationTemplate::query()->create([
            'name' => 'Password Reset',
            'slug' => 'password-reset',
            'type' => 'email',
            'event' => 'password_reset',
            'subject' => ['en' => 'Reset link for {{email}}'],
            'content' => ['en' => 'Click here {{url}}'],
            'variables' => ['email', 'url'],
            'is_active' => true,
        ])->fresh();

        // Provide replacement variables to confirm the rendered strings include the supplied values.
        $variables = ['email' => 'user@example.test', 'url' => 'https://example.test/reset'];

        self::assertSame('Reset link for user@example.test', $template->renderSubject($variables, 'en'));
        self::assertSame('Click here https://example.test/reset', $template->renderContent($variables, 'en'));
    }

    public function test_scopes_filter_and_sort_records(): void
    {
        // Seed multiple templates so scope combinations can be validated independently.
        NotificationTemplate::query()->create([
            'name' => 'A Template',
            'slug' => 'a-template',
            'type' => 'email',
            'event' => 'user_registered',
            'subject' => ['en' => 'A'],
            'content' => ['en' => 'A'],
            'variables' => ['name'],
            'is_active' => true,
        ]);

        NotificationTemplate::query()->create([
            'name' => 'C Template',
            'slug' => 'c-template',
            'type' => 'sms',
            'event' => 'order_created',
            'subject' => ['en' => 'C'],
            'content' => ['en' => 'C'],
            'variables' => ['code'],
            'is_active' => false,
        ]);

        NotificationTemplate::query()->create([
            'name' => 'B Template',
            'slug' => 'b-template',
            'type' => 'email',
            'event' => 'order_created',
            'subject' => ['en' => 'B'],
            'content' => ['en' => 'B'],
            'variables' => ['order'],
            'is_active' => true,
        ]);

        // Active scope should only include records explicitly marked as active.
        self::assertSame(2, NotificationTemplate::query()->active()->count());

        // Type and event scopes should refine the query set appropriately.
        self::assertSame(2, NotificationTemplate::query()->byType('email')->count());
        self::assertSame(2, NotificationTemplate::query()->byEvent('order_created')->count());

        // Ordered scope should return names in alphabetical order regardless of insertion order.
        $orderedNames = NotificationTemplate::query()->orderedByName()->pluck('name')->all();
        self::assertSame(['A Template', 'B Template', 'C Template'], $orderedNames);
    }

    public function test_get_by_event_returns_only_active_template(): void
    {
        // Create an inactive template followed by an active one to confirm the helper respects the status.
        NotificationTemplate::query()->create([
            'name' => 'Inactive Password Reset',
            'slug' => 'inactive-password-reset',
            'type' => 'email',
            'event' => 'password_reset',
            'subject' => ['en' => 'Inactive'],
            'content' => ['en' => 'Inactive'],
            'variables' => ['name'],
            'is_active' => false,
        ]);

        $activeTemplate = NotificationTemplate::query()->create([
            'name' => 'Active Password Reset',
            'slug' => 'active-password-reset',
            'type' => 'email',
            'event' => 'password_reset',
            'subject' => ['en' => 'Active'],
            'content' => ['en' => 'Active'],
            'variables' => ['name'],
            'is_active' => true,
        ])->fresh();

        // The helper should ignore inactive rows and return the active instance.
        self::assertTrue($activeTemplate->is(NotificationTemplate::getByEvent('password_reset')));
    }

    public function test_get_available_variables_normalizes_values(): void
    {
        // Store data with whitespace and duplicate entries to ensure normalization happens.
        $template = NotificationTemplate::query()->create([
            'name' => 'Variables Example',
            'slug' => 'variables-example',
            'type' => 'email',
            'event' => 'example_event',
            'subject' => ['en' => 'Subject'],
            'content' => ['en' => 'Content'],
            'variables' => [' name ', 'email', 'email', 123],
            'is_active' => true,
        ])->fresh();

        // Expect duplicates to be removed and values trimmed while casting numbers to strings.
        self::assertSame(['name', 'email', '123'], $template->getAvailableVariables());
    }
}

