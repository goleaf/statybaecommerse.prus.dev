<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Filesystem;

use App\Support\Filesystem\FilesystemPermissions;
use Tests\TestCase;

/**
 * Test FilesystemPermissions functionality.
 *
 * Covers permission constants and configuration options.
 */
final class FilesystemPermissionsTest extends TestCase
{
    public function test_default_permissions(): void
    {
        $permissions = FilesystemPermissions::default();

        $this->assertEquals(0755, $permissions->getDirectoryMode());
        $this->assertEquals(0644, $permissions->getFileMode());
    }

    public function test_secure_permissions(): void
    {
        $permissions = FilesystemPermissions::secure();

        $this->assertEquals(0750, $permissions->getDirectoryMode());
        $this->assertEquals(0640, $permissions->getFileMode());
    }

    public function test_custom_permissions(): void
    {
        $permissions = new FilesystemPermissions(0700, 0600);

        $this->assertEquals(0700, $permissions->getDirectoryMode());
        $this->assertEquals(0600, $permissions->getFileMode());
    }

    public function test_constants_are_defined(): void
    {
        $this->assertEquals(0755, FilesystemPermissions::DEFAULT_DIRECTORY_MODE);
        $this->assertEquals(0644, FilesystemPermissions::DEFAULT_FILE_MODE);
        $this->assertEquals(0750, FilesystemPermissions::SECURE_DIRECTORY_MODE);
        $this->assertEquals(0640, FilesystemPermissions::SECURE_FILE_MODE);
    }

    public function test_constructor_with_defaults(): void
    {
        $permissions = new FilesystemPermissions;

        $this->assertEquals(FilesystemPermissions::DEFAULT_DIRECTORY_MODE, $permissions->getDirectoryMode());
        $this->assertEquals(FilesystemPermissions::DEFAULT_FILE_MODE, $permissions->getFileMode());
    }
}
