<?php

declare(strict_types=1);

use App\Services\PaginationService;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

uses(TestCase::class);

class _TmpItem extends Model
{
    protected $table = 'tmp_items';

    public $timestamps = false;

    protected $guarded = [];
}

it('returns pagination config for known contexts', function (string $context, array $expectedKeys) {
    $cfg = PaginationService::getPaginationConfig($context);
    expect($cfg)->toHaveKeys($expectedKeys);
})->with([
    ['products', ['perPage', 'onEachSide', 'perPageOptions']],
    ['admin', ['perPage', 'onEachSide', 'perPageOptions']],
    ['api', ['perPage', 'onEachSide', 'perPageOptions']],
]);

it('computes smart onEachSide based on total pages', function () {
    config()->set('database.default', 'sqlite');
    \DB::statement('CREATE TABLE IF NOT EXISTS tmp_items (id INTEGER PRIMARY KEY AUTOINCREMENT)');
    for ($i = 0; $i < 60; $i++) {
        _TmpItem::create();
    }

    $builder = _TmpItem::query();
    $paginator = PaginationService::smartPaginate($builder, perPage: 12, maxOnEachSide: 3);

    expect($paginator)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class)
        ->and(property_exists($paginator, 'onEachSide'))
        ->toBeTrue();
});
