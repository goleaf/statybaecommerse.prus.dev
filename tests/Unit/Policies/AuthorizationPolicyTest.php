<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\DiscountCondition;
use App\Models\Export;
use App\Models\Legal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductRequest;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\AddressPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CountryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DiscountConditionPolicy;
use App\Policies\ExportPolicy;
use App\Policies\LegalPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductHistoryPolicy;
use App\Policies\ProductRequestPolicy;
use App\Policies\ReferralCodePolicy;
use App\Policies\ReferralPolicy;
use App\Policies\RolePolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use App\Support\Authorization\AuthorizationMatrix;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as PermissionRole;

/**
 * Preserve the original Gate "before" callbacks so policy tests do not leak mutations
 * into unrelated suites that expect the core authorization hooks to remain intact.
 *
 * @var array<int, callable>|null $originalGateBeforeCallbacks
 */
$originalGateBeforeCallbacks = null;

beforeEach(function () use (&$originalGateBeforeCallbacks): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    config()->set('authorization.testing.skip_checks', false);

    $gate = Gate::getFacadeRoot();

    if ($gate === null) {
        // Bail early when the Gate facade has not been resolved by the container yet.
        $originalGateBeforeCallbacks = null;

        return;
    }

    $reflection = new ReflectionClass($gate);

    if (! $reflection->hasProperty('beforeCallbacks')) {
        // Nothing to strip if the property is unavailable on the current Gate implementation.
        $originalGateBeforeCallbacks = null;

        return;
    }

    $property = $reflection->getProperty('beforeCallbacks');
    $property->setAccessible(true);

    $callbacks = $property->getValue($gate);

    // Capture the original callbacks to restore them in the matching afterEach hook.
    $originalGateBeforeCallbacks = is_array($callbacks) ? $callbacks : null;

    $property->setValue($gate, []);

    $abilityChecks = [
        'download exports',
        'manage exports',
        'view_customers',
        'create_customers',
        'edit_customers',
        'delete_customers',
    ];

    foreach ($abilityChecks as $ability) {
        if (! Gate::has($ability)) {
            Gate::define($ability, static function ($user) use ($ability): bool {
                if ((method_exists($user, 'getAttribute') && (bool) $user->getAttribute('is_admin'))
                    || (isset($user->is_admin) && (bool) $user->is_admin)) {
                    return true;
                }

                $permissions = $user->getRelationValue('permissions');

                if (! $permissions instanceof \Illuminate\Support\Collection) {
                    return false;
                }

                return $permissions->contains(static function ($permission) use ($ability): bool {
                    return $permission instanceof Permission && $permission->name === $ability;
                });
            });
        }
    }
});

afterEach(function () use (&$originalGateBeforeCallbacks): void {
    if ($originalGateBeforeCallbacks === null) {
        // Skip restoration when the before callbacks were absent or the gate never resolved.
        return;
    }

    $gate = Gate::getFacadeRoot();

    if ($gate === null) {
        // Reset the cached snapshot to avoid leaking stale references into later tests.
        $originalGateBeforeCallbacks = null;

        return;
    }

    $reflection = new ReflectionClass($gate);

    if (! $reflection->hasProperty('beforeCallbacks')) {
        $originalGateBeforeCallbacks = null;

        return;
    }

    $property = $reflection->getProperty('beforeCallbacks');
    $property->setAccessible(true);

    // Restore the original callbacks to reinstate the application's authorization behaviour.
    $property->setValue($gate, $originalGateBeforeCallbacks);

    $originalGateBeforeCallbacks = null;
});

function policyUser(string $role, array $permissions = []): User
{
    $user = User::factory()->make([
        'id' => random_int(1, PHP_INT_MAX),
    ]);

    attachRoleAndPermissions($user, 'web', $role, $permissions);

    return $user;
}

function policyAdmin(string $role = 'admin', array $permissions = []): AdminUser
{
    $admin = AdminUser::factory()->make([
        'id' => random_int(1, PHP_INT_MAX),
    ]);

    attachRoleAndPermissions($admin, 'admin', $role, $permissions);

    return $admin;
}

/**
 * Attach in-memory role and permission records so policy tests can exercise gate checks without database writes.
 */
function attachRoleAndPermissions(User|AdminUser $user, string $guard, string $roleName, array $permissions): void
{
    // Fabricate an unsaved role model so HasRoles::getRoleNames() resolves the expected identifier in memory.
    $roleModel = PermissionRole::make([
        'name'       => $roleName,
        'guard_name' => $guard,
    ]);

    // Mirror the permission instances on both the role and the user so calls to can() succeed without persisting records.
    $permissionModels = collect($permissions)
        ->map(static fn (string $permission): Permission => Permission::make([
            'name'       => $permission,
            'guard_name' => $guard,
        ]))
        ->values();

    $roleModel->setRelation('permissions', $permissionModels);
    $user->setRelation('permissions', collect($permissionModels->all()));

    $user->setRelation('roles', collect([$roleModel]));
}

it('unit: grants admins full catalog access', function (): void {
    $admin = policyUser('admin');
    $product = Product::factory()->make();
    $category = Category::factory()->make();
    $brand = Brand::factory()->make();

    $productPolicy = app(ProductPolicy::class);
    $categoryPolicy = app(CategoryPolicy::class);
    $brandPolicy = app(BrandPolicy::class);

    expect($productPolicy->create($admin))->toBeTrue()
        ->and($productPolicy->update($admin, $product))->toBeTrue()
        ->and($productPolicy->delete($admin, $product))->toBeTrue();

    expect($categoryPolicy->create($admin))->toBeTrue()
        ->and($brandPolicy->update($admin, $brand))->toBeTrue();
});

it('unit: limits managers to non-destructive catalog changes', function (): void {
    $manager = policyUser('manager');
    $product = Product::factory()->make();
    $order = Order::factory()->make();

    $productPolicy = app(ProductPolicy::class);
    $orderPolicy = app(OrderPolicy::class);

    expect($productPolicy->create($manager))->toBeTrue()
        ->and($productPolicy->update($manager, $product))->toBeTrue()
        ->and($productPolicy->delete($manager, $product))->toBeFalse();

    expect($orderPolicy->update($manager, $order))->toBeTrue()
        ->and($orderPolicy->delete($manager, $order))->toBeFalse();
});

it('unit: restricts editors to updates only', function (): void {
    $editor = policyUser('editor');
    $product = Product::factory()->make();

    $productPolicy = app(ProductPolicy::class);

    expect($productPolicy->create($editor))->toBeFalse()
        ->and($productPolicy->update($editor, $product))->toBeTrue()
        ->and($productPolicy->delete($editor, $product))->toBeFalse();
});

it('unit: allows viewers to read but not modify data', function (): void {
    $viewer = policyUser('viewer');
    $product = Product::factory()->make();
    $order = Order::factory()->make();
    $userPolicy = app(UserPolicy::class);

    expect(app(ProductPolicy::class)->view($viewer, $product))->toBeTrue()
        ->and(app(ProductPolicy::class)->update($viewer, $product))->toBeFalse();

    expect(app(OrderPolicy::class)->view($viewer, $order))->toBeTrue()
        ->and(app(OrderPolicy::class)->update($viewer, $order))->toBeFalse();

    expect($userPolicy->viewAny($viewer))->toBeFalse();
});

it('unit: storefront users can manage their own addresses without global permissions', function (): void {
    $addressOwner = policyUser('user');
    $address = Address::factory()->make([
        'user_id' => $addressOwner->getKey(),
    ]);

    $addressPolicy = app(AddressPolicy::class);

    expect($addressPolicy->view($addressOwner, $address))->toBeTrue()
        ->and($addressPolicy->update($addressOwner, $address))->toBeTrue();

    $otherUser = policyUser('user');

    expect($addressPolicy->update($otherUser, $address))->toBeFalse();
});

it('unit: matrix grants admin settings access while blocking viewers', function (): void {
    $admin = policyUser('admin');
    $viewer = policyUser('viewer');
    $setting = SystemSetting::factory()->make();

    $policy = app(SystemSettingPolicy::class);

    expect($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $setting))->toBeTrue();

    expect($policy->viewAny($viewer))->toBeFalse();
});

it('unit: notification policies honour both matrix permissions and ownership rules', function (): void {
    $admin = policyUser('admin');
    $recipient = policyUser('user');
    $viewer = policyUser('viewer');

    $notification = Notification::factory()->make([
        'notifiable_type' => User::class,
        'notifiable_id'   => $recipient->getKey(),
    ]);

    $policy = app(NotificationPolicy::class);

    expect($policy->viewAny($recipient))->toBeTrue()
        ->and($policy->markAsRead($recipient, $notification))->toBeTrue();

    expect($policy->markAsRead($viewer, $notification))->toBeFalse();

    expect($policy->markAsUnread($admin, $notification))->toBeTrue();
});

it('unit: product request policies combine matrix checks with ownership fallbacks', function (): void {
    $admin = policyUser('admin');
    $owner = policyUser('user');
    $request = ProductRequest::factory()->make([
        'user_id' => $owner->getKey(),
    ]);

    $policy = app(ProductRequestPolicy::class);

    expect($policy->view($owner, $request))->toBeTrue()
        ->and($policy->update($owner, $request))->toBeTrue();

    expect($policy->respond($admin, $request))->toBeTrue();
});

it('unit: role policy honours matrix driven maintenance abilities', function (): void {
    // Create representative users with elevated and read-only permissions.
    $admin = policyUser('admin');
    $viewer = policyUser('viewer');

    // Build an in-memory role model to satisfy policy signatures.
    $role = \Spatie\Permission\Models\Role::make([
        'name'       => 'sample-role',
        'guard_name' => 'web',
    ]);

    $policy = app(RolePolicy::class);

    expect($policy->restore($admin, $role))->toBeTrue()
        ->and($policy->forceDelete($admin, $role))->toBeTrue()
        ->and($policy->forceDeleteAny($admin))->toBeTrue()
        ->and($policy->restoreAny($admin))->toBeTrue()
        ->and($policy->replicate($admin, $role))->toBeTrue()
        ->and($policy->reorder($admin))->toBeTrue();

    expect($policy->restore($viewer, $role))->toBeFalse()
        ->and($policy->forceDelete($viewer, $role))->toBeFalse()
        ->and($policy->replicate($viewer, $role))->toBeFalse();
});

it('unit: audit log visibility stays limited to privileged operators', function (): void {
    $admin = policyAdmin('admin');
    $viewer = policyUser('viewer');
    $auditLog = AuditLog::make([
        'entity_type' => Product::class,
        'entity_id'   => '1',
        'action'      => 'updated',
    ]);

    $policy = app(AuditLogPolicy::class);

    // Administrators should browse the audit trail across list and detail views.
    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->view($admin, $auditLog))->toBeTrue();

    // Read-only viewers must never enumerate or inspect sensitive audit records.
    expect($policy->viewAny($viewer))->toBeFalse()
        ->and($policy->view($viewer, $auditLog))->toBeFalse();
});

it('unit: country policy keeps geographic datasets fully public', function (): void {
    $policy = app(CountryPolicy::class);
    $country = Country::factory()->make();

    // Guests and authenticated users alike can browse all country endpoints.
    expect($policy->viewAny(null))->toBeTrue()
        ->and($policy->view(null, $country))->toBeTrue()
        ->and($policy->viewStatistics(null))->toBeTrue()
        ->and($policy->viewEuMembers(null))->toBeTrue()
        ->and($policy->viewVatCountries(null))->toBeTrue();
});

it('unit: customer policy honours ownership while letting admins manage records', function (): void {
    $owner = policyUser('user');
    $customer = Customer::factory()->make([
        'user_id' => $owner->getKey(),
    ]);

    $policy = app(CustomerPolicy::class);

    // A storefront user can always manage their own linked customer profile.
    expect($policy->view($owner, $customer))->toBeTrue()
        ->and($policy->update($owner, $customer))->toBeTrue()
        ->and($policy->delete($owner, $customer))->toBeTrue();

    $stranger = policyUser('user');

    // Other customers without explicit permissions cannot inspect the record.
    expect($policy->view($stranger, $customer))->toBeFalse()
        ->and($policy->update($stranger, $customer))->toBeFalse();

    $administrator = policyAdmin('admin');
    $administrator->forceFill(['is_admin' => true]);

    // Administrators bypass the permission gate for maintenance workflows.
    expect($policy->viewAny($administrator))->toBeTrue()
        ->and($policy->create($administrator))->toBeTrue()
        ->and($policy->delete($administrator, $customer))->toBeTrue();

    // Regular users can still create their own customer entry via the storefront.
    expect($policy->create($owner))->toBeTrue();
});

it('unit: discount condition policy restricts access to authorised staff', function (): void {
    $admin = policyUser('admin');
    $unauthorised = policyUser('user');
    $condition = DiscountCondition::factory()->make();

    $policy = app(DiscountConditionPolicy::class);

    // Admin-level roles can audit and review every discount condition entry.
    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->view($admin, $condition))->toBeTrue();

    // Storefront users without elevated permissions cannot inspect the diagnostic helpers.
    expect($policy->viewAny($unauthorised))->toBeFalse()
        ->and($policy->view($unauthorised, $condition))->toBeFalse();
});

it('unit: export policy allows request owners or explicitly permitted users', function (): void {
    $owner = policyUser('user');
    $ownExport = Export::factory()->make([
        'requested_by' => $owner->getKey(),
    ]);

    $policy = app(ExportPolicy::class);

    // The actor who requested an export is always allowed to download it.
    expect($policy->download($owner, $ownExport))->toBeTrue();

    $permitted = policyUser('user', ['download exports']);
    // Toggle the least-significant bit so the requester id differs without risking integer overflow.
    $otherRequesterId = $owner->getKey() ^ 1;
    if ($otherRequesterId === 0) {
        $otherRequesterId = 2;
    }
    $sharedExport = Export::factory()->make([
        'requested_by' => $otherRequesterId,
    ]);

    // Users with the dedicated permission can access third-party exports.
    expect($policy->download($permitted, $sharedExport))->toBeTrue();

    $denied = policyUser('user');

    // Unrelated users lacking permissions cannot access someone else's export.
    expect($policy->download($denied, $sharedExport))->toBeFalse();
});

it('unit: legal policy leaves public reading open but guards editorial actions', function (): void {
    $policy = app(LegalPolicy::class);
    $legal = Legal::factory()->make();

    $customer = policyUser('user');

    // Storefront users may read legal pages regardless of admin privileges.
    expect($policy->view($customer, $legal))->toBeTrue();

    $admin = policyAdmin('admin');
    $admin->forceFill(['is_admin' => true]);

    // Administrative staff can perform all CRUD operations on legal entries.
    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $legal))->toBeTrue();

    $restrictedAdmin = policyAdmin('viewer');

    // Operators without permissions are denied destructive management access.
    expect($policy->create($restrictedAdmin))->toBeFalse()
        ->and($policy->delete($restrictedAdmin, $legal))->toBeFalse();
});

it('unit: product history policy requires both history and product privileges', function (): void {
    $admin = policyUser('admin');
    $viewer = policyUser('viewer');
    $product = Product::factory()->make([
        'id' => random_int(1, PHP_INT_MAX),
    ]);
    $history = ProductHistory::factory()->make([
        'product_id' => $product->getKey(),
    ]);

    $policy = app(ProductHistoryPolicy::class);

    // Admins with catalog access can browse, export, and append history entries.
    expect($policy->viewAny($admin, $product))->toBeTrue()
        ->and($policy->view($admin, $history, $product))->toBeTrue()
        ->and($policy->statistics($admin, $product))->toBeTrue()
        ->and($policy->export($admin, $product))->toBeTrue()
        ->and($policy->create($admin, $product))->toBeTrue();

    // Flip the lowest bit to craft a different product identifier for the mismatch assertion.
    $otherProductId = $product->getKey() ^ 1;
    if ($otherProductId === 0) {
        $otherProductId = 2;
    }
    $foreignHistory = ProductHistory::factory()->make([
        'product_id' => $otherProductId,
    ]);

    // Cross-product lookups must be rejected to prevent data leakage.
    expect($policy->view($admin, $foreignHistory, $product))->toBeFalse();

    // Read-only viewers cannot access detailed history features.
    expect($policy->viewAny($viewer, $product))->toBeFalse()
        ->and($policy->create($viewer, $product))->toBeFalse();
});

it('unit: referral policy enforces ownership while empowering administrators', function (): void {
    $referrer = policyUser('user');
    $referred = policyUser('user');
    $referral = Referral::factory()->make([
        'referrer_id' => $referrer->getKey(),
        'referred_id' => $referred->getKey(),
    ]);

    $policy = app(ReferralPolicy::class);

    $admin = policyAdmin('admin');
    $admin->forceFill(['is_admin' => true]);

    // Administrators retain full control over any referral lifecycle action.
    expect($policy->view($admin, $referral))->toBeTrue()
        ->and($policy->update($admin, $referral))->toBeTrue()
        ->and($policy->delete($admin, $referral))->toBeTrue()
        ->and($policy->restore($admin, $referral))->toBeTrue();

    // The referrer can view and update their own invitations.
    expect($policy->view($referrer, $referral))->toBeTrue()
        ->and($policy->update($referrer, $referral))->toBeTrue();

    // The referred customer can inspect but not modify the referral entry.
    expect($policy->view($referred, $referral))->toBeTrue()
        ->and($policy->update($referred, $referral))->toBeFalse();

    $stranger = policyUser('user');

    // Completely unrelated users cannot view or delete the referral.
    expect($policy->view($stranger, $referral))->toBeFalse()
        ->and($policy->delete($stranger, $referral))->toBeFalse();
});

it('unit: referral code policy limits management to owners or admins', function (): void {
    $owner = policyUser('user');
    $code = ReferralCode::factory()->make([
        'user_id' => $owner->getKey(),
    ]);

    $policy = app(ReferralCodePolicy::class);

    // Code owners can view and update their own referral identifiers.
    expect($policy->view($owner, $code))->toBeTrue()
        ->and($policy->update($owner, $code))->toBeTrue()
        ->and($policy->delete($owner, $code))->toBeTrue();

    $admin = policyAdmin('admin');
    $admin->forceFill(['is_admin' => true]);

    // Administrators can perform destructive lifecycle actions on any code.
    expect($policy->view($admin, $code))->toBeTrue()
        ->and($policy->forceDelete($admin, $code))->toBeTrue();

    $other = policyUser('user');

    // Other customers cannot inspect or delete someone else's referral code.
    expect($policy->view($other, $code))->toBeFalse()
        ->and($policy->delete($other, $code))->toBeFalse();

    // Creation remains open to all authenticated users for onboarding flows.
    expect($policy->create($other))->toBeTrue();
});

it('unit: authorization matrix maps advanced role actions to canonical permissions', function (): void {
    // Confirm each advanced ability references an existing permission string.
    expect(AuthorizationMatrix::ability('roles', 'restore'))->toBe('roles.update')
        ->and(AuthorizationMatrix::ability('roles', 'forceDelete'))->toBe('roles.delete')
        ->and(AuthorizationMatrix::ability('roles', 'forceDeleteAny'))->toBe('roles.delete')
        ->and(AuthorizationMatrix::ability('roles', 'restoreAny'))->toBe('roles.update')
        ->and(AuthorizationMatrix::ability('roles', 'replicate'))->toBe('roles.create')
        ->and(AuthorizationMatrix::ability('roles', 'reorder'))->toBe('roles.update');
});
