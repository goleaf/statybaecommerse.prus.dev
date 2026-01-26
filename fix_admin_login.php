<?php

use App\Models\User;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing admin login...\n";

// Fix User (web guard)
try {
    $user = User::withTrashed()->where('email', 'admin@example.com')->first();
    if (!$user) {
        $user = new User();
        $user->email = 'admin@example.com';
        $user->name = 'Admin';
        echo "Creating new User record...\n";
    } else {
        if ($user->trashed()) {
            $user->restore();
            echo "Restored trashed User record...\n";
        }
        echo "Updating existing User record...\n";
    }

    $user->password = 'Admin123!'; // 'hashed' cast should handle this
    $user->email_verified_at = now();
    $user->is_admin = true;
    $user->is_active = true;
    $user->save();
    
    echo "User (web) fixed. ID: {$user->id}\n";
} catch (\Exception $e) {
    echo "Error fixing User: " . $e->getMessage() . "\n";
}

// Fix AdminUser (admin guard)
try {
    if (class_exists(AdminUser::class)) {
        $admin = AdminUser::query()->where('email', 'admin@example.com')->first();
        if (!$admin) {
            $admin = new AdminUser();
            $admin->email = 'admin@example.com';
            $admin->name = 'Administrator';
            echo "Creating new AdminUser record...\n";
        } else {
            echo "Updating existing AdminUser record...\n";
        }

        $admin->password = 'Admin123!'; // 'hashed' cast should handle this
        $admin->email_verified_at = now();
        $admin->save();

        echo "AdminUser (admin) fixed. ID: {$admin->id}\n";
    } else {
        echo "AdminUser model not found.\n";
    }
} catch (\Exception $e) {
    echo "Error fixing AdminUser: " . $e->getMessage() . "\n";
}

echo "Done.\n";
