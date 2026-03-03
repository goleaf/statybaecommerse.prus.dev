<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brochure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

final class BrochureController extends Controller
{
    public function index(): View
    {
        $brochures = Brochure::query()
            ->active()
            ->whereHas('files', static fn (Builder $query): Builder => $query->active())
            ->with([
                'files' => static fn ($filesQuery) => $filesQuery
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $stats = [
            'total_brochures' => $brochures->count(),
            'total_files'     => $brochures->sum(static fn (Brochure $brochure): int => $brochure->files->count()),
        ];

        return view('frontend.brochures.index', [
            'brochures' => $brochures,
            'stats'     => $stats,
        ]);
    }
}
