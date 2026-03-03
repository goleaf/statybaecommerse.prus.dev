<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brochure;
use Illuminate\Contracts\View\View;

final class BrochureController extends Controller
{
    public function index(): View
    {
        $brochures = Brochure::query()
            ->active()
            ->whereHas('files', static fn ($query) => $query->active())
            ->with([
                'files' => static fn ($query) => $query
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $downloads = $brochures
            ->flatMap(static fn (Brochure $brochure) => $brochure->files->map(static function ($file) use ($brochure): array {
                return [
                    'brochure_title' => $brochure->title,
                    'file'           => $file,
                ];
            }))
            ->values();

        return view('frontend.brochures.index', [
            'downloads' => $downloads,
        ]);
    }
}
