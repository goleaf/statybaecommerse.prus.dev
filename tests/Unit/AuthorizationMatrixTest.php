<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AuthorizationRole;
use App\Support\Authorization\AuthorizationMatrix;
use PHPUnit\Framework\TestCase;

final class AuthorizationMatrixTest extends TestCase
{
    public function test_permissions_are_exposed_for_known_roles(): void
    {
        $adminPermissions = AuthorizationMatrix::permissionsForRole(AuthorizationRole::ADMIN);

        $this->assertContains('create_products', $adminPermissions);
        $this->assertContains('orders.update', $adminPermissions);
    }

    public function test_support_role_does_not_receive_product_create_permission(): void
    {
        $supportPermissions = AuthorizationMatrix::permissionsForRole(AuthorizationRole::SUPPORT);

        $this->assertNotContains('create_products', $supportPermissions);
        $this->assertContains('orders.update', $supportPermissions);
    }

    public function test_guard_names_are_configured(): void
    {
        $guards = AuthorizationMatrix::guardNames();

        $this->assertContains('admin', $guards);
        $this->assertContains('web', $guards);
    }
}
