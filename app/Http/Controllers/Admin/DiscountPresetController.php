<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Discounts\DiscountPresetRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * DiscountPresetController
 *
 * Responsible for presenting and managing reusable discount preset
 * configurations within the admin area.
 */
final class DiscountPresetController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly DiscountPresetRepository $repository)
    {
        // The repository is injected to allow easy testing and to encapsulate
        // how presets are stored (filesystem, database, etc.).
    }

    /**
     * Display all available discount presets.
     */
    public function index(): View
    {
        // Retrieve every preset so the view can render an overview table.
        $presets = $this->repository->all();

        return view('admin.discounts.presets.index', [
            'presets' => $presets,
        ]);
    }

    /**
     * Store a new discount preset based on validated administrator input.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the preset data to ensure consistent formatting and
        // to protect against invalid values that could break calculations.
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type'        => ['required', Rule::in(['percentage', 'fixed'])],
            'value'       => ['required', 'numeric', 'min:0'],
            'conditions'  => ['nullable', 'string', 'max:1000'],
        ]);

        // Normalise the conditions list by splitting multiline input into
        // individual entries, trimming whitespace, and dropping empties.
        $validated['conditions'] = collect([
            $validated['conditions'] ?? '',
        ])
            ->flatMap(static fn ($value): array => preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn ($value): bool => $value !== '')
            ->values()
            ->all();

        // Persist the preset using the repository abstraction.
        $this->repository->create($validated);

        // Redirect back to the index with a friendly flash message.
        return redirect()
            ->route('admin.discounts.presets')
            ->with('status', __('Discount preset saved successfully.'));
    }
}
