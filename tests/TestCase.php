<?php declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	protected function setUp(): void
	{
		parent::setUp();
		$this->withoutMiddleware([
			\App\Http\Middleware\ZoneDetector::class,
			\App\Http\Middleware\SetLocale::class,
			\Spatie\Permission\Middleware\PermissionMiddleware::class,
			\Spatie\Permission\Middleware\RoleMiddleware::class,
			\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
		]);
	}
}
