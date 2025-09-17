<?php declare(strict_types=1);

use App\Models\Report;

it('casts attributes correctly', function (): void {
    $report = Report::factory()->make([
        'filters' => ['status' => 'paid'],
        'is_active' => true,
    ]);

    expect($report->filters)
        ->toBeArray()
        ->and($report->is_active)
        ->toBeTrue();
});

it('fillable allows mass assignment', function (): void {
    $data = Report::factory()->make()->toArray();
    $report = new Report($data);
    expect($report->getTranslation('name', 'lt'))->toBe($data['name']['lt']);
});
