<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DocumentSeeder;
use Database\Seeders\DocumentTemplateSeeder;

it('seeds documents using factories and relationships', function (): void {
    $this->seed(DocumentTemplateSeeder::class);
    $orders = Order::factory()->count(5)->create();
    $users = User::factory()->count(3)->create();

    $this->seed(DocumentSeeder::class);

    $documents = Document::query()
        ->with(['template', 'documentable', 'creator'])
        ->get();

    expect($documents)->not->toBeEmpty();

    $documents->each(function (Document $document) use ($orders, $users): void {
        expect($document->template)->not->toBeNull();
        expect($document->documentable)->not->toBeNull();
        expect($document->creator)->not->toBeNull();
        expect($document->documentable_id)->toBeIn($orders->pluck('id'));
        expect($document->created_by)->toBeIn($users->pluck('id'));
    });
});
