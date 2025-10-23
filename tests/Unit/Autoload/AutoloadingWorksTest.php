<?php

declare(strict_types=1);

namespace Tests\Unit\Autoload;

use App\Filament\AdminPanelProvider;
use App\Livewire\Components\Navigation;
use App\Models\Product;
use App\Application\Product\DTOs\PaginationDto;
use App\Support\Security\CspNonce;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

final class AutoloadingWorksTest extends PhpUnitTestCase
{
    public function test_classes_are_autoloadable(): void
    {
        $this->assertTrue(class_exists(Product::class), 'Product model should autoload.');
        $this->assertTrue(class_exists(Navigation::class), 'Livewire component should autoload.');
        $this->assertTrue(class_exists(AdminPanelProvider::class), 'Filament provider should autoload.');
        $this->assertTrue(class_exists(\App\Domain\Product\Entities\Product::class), 'Domain entity should autoload.');
    }

    public function test_can_instantiate_key_classes(): void
    {
        $nonce = new CspNonce();
        $this->assertNotEmpty($nonce->value());
        $this->assertStringStartsWith("'nonce-", $nonce->headerValue());

        $nav = new Navigation();
        $this->assertInstanceOf(Navigation::class, $nav);

        $dto = new PaginationDto(100, 10, 1);
        $this->assertSame(100, $dto->getTotal());
    }
}
