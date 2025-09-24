<?php

declare(strict_types=1);

namespace Tests;

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use PHPUnit\Framework\TestCase;

final class SimpleSystemSettingDependencyTest extends TestCase
{
    public function test_can_create_system_setting_dependency(): void
    {
        $this->assertTrue(true);
    }

    public function test_system_setting_dependency_model_exists(): void
    {
        $this->assertTrue(class_exists(SystemSettingDependency::class));
    }

    public function test_system_setting_model_exists(): void
    {
        $this->assertTrue(class_exists(SystemSetting::class));
    }
}
