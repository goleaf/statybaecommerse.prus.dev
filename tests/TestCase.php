<?php declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	protected function setUp(): void
	{
		parent::setUp();
		Config::set('database.default', 'sqlite');
		Config::set('database.connections.sqlite.database', ':memory:');
		$this->withoutMiddleware([
			\App\Http\Middleware\ZoneDetector::class,
			\App\Http\Middleware\SetLocale::class,
			\Spatie\Permission\Middleware\PermissionMiddleware::class,
			\Spatie\Permission\Middleware\RoleMiddleware::class,
			\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
		]);
	}
}
