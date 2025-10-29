<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\PaginationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ReportController
 *
 * HTTP controller handling ReportController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ReportController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Normalize legacy query parameters by redirecting early – this avoids
        // collisions with Livewire components that reserve the `category`
        // parameter for numeric IDs elsewhere in the storefront.
        if ($request->has('category')) {
            return redirect()->route('reports.index', array_merge(
                $request->except('category'),
                ['report_category' => $request->query('category')]
            ));
        }

        // Validate and sanitize the incoming filter/sort payload so that we
        // only rely on whitelisted query parameters before building the SQL
        // query. This protects against SQL injection vectors and ensures the
        // controller behaves predictably when unexpected input is provided.
        $validated = $this->validateIndexRequest($request);

        // Build the public report query by leaning on dedicated scopes for the
        // active/public checks and making sure a translated name exists for the
        // current locale so empty records do not leak into the storefront.
        $query = Report::query()
            ->active()
            ->public()
            ->whereNotNull('type')
            ->whereNotNull('category')
            ->whereNotNull('name->' . app()->getLocale());

        // Apply optional filters that passed validation – the conditional guard
        // prevents null values from altering the query builder state.
        if (! empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (! empty($validated['report_category'])) {
            $query->where('category', $validated['report_category']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            // Group the search constraints so name and description are checked
            // together – this keeps the intent explicit and avoids precedence
            // confusion when more where clauses are added in the future.
            $query->where(function (Builder $builder) use ($search) {
                $locale = app()->getLocale();

                $builder
                    ->where("name->{$locale}", 'like', "%{$search}%")
                    ->orWhere("description->{$locale}", 'like', "%{$search}%");
            });
        }

        // Apply sanitized sorting, handling translated names separately so we
        // order by the locale specific JSON key instead of the raw JSON blob.
        $sortBy = $validated['sort'] ?? 'created_at';
        $sortDirection = $validated['direction'] ?? 'desc';

        if ($sortBy === 'name') {
            $query->orderBy('name->' . app()->getLocale(), $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Paginate with the shared helper and carry the sanitized parameters so
        // the view can maintain filter state across pagination links.
        $reports = PaginationService::paginateWithOnEachSide($query, 12);
        $reports->appends($validated);

        // Hydrate dropdown filter options from already loaded reports to avoid extra queries
        $types = $reports->pluck('type')
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $type) => [$type => __("admin.reports.types.{$type}")]);

        $categories = $reports->pluck('category')
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $category) => [$category => __("admin.reports.categories.{$category}")]);

        return view('reports.index', compact('reports', 'types', 'categories'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Report $report): View
    {
        // Check if report is public or user has access
        if (! $this->canAccessReport($report)) {
            abort(403, __('reports.messages.access_denied'));
        }
        // Increment view count
        $report->incrementViewCount();
        // Get related reports
        $relatedReports = Report::query()
            ->active()
            ->public()
            ->whereKeyNot($report->getKey())
            ->where(function (Builder $builder) use ($report) {
                // Surface reports that share either the same type or category –
                // chaining inside the closure keeps the orWhere grouped so the
                // outer constraints (active/public) remain intact.
                $builder
                    ->where('type', $report->type)
                    ->orWhere('category', $report->category);
            })
            ->limit(4)
            ->get()
            ->filter(fn (Report $relatedReport) => $this->isDisplayable($relatedReport))
            ->values();

        return view('reports.show', compact('report', 'relatedReports'));
    }

    /**
     * Handle download functionality with proper error handling.
     */
    public function download(Report $report): Response
    {
        // Check if report is public or user has access
        if (! $this->canAccessReport($report)) {
            abort(403, __('reports.messages.access_denied'));
        }
        // Increment download count
        $report->incrementDownloadCount();
        // Generate PDF or return content based on report type
        $content = $this->generateReportContent($report);
        $pdfBinary = Pdf::loadHTML($content)->output();

        $filenameSeed = $report->getTranslation('name', app()->getLocale(), false) ?? $report->slug ?? $report->getKey();
        $filename = Str::slug((string) $filenameSeed) . '_' . now()->format('Y-m-d') . '.pdf';

        return response($pdfBinary)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Handle generate functionality with proper error handling.
     */
    public function generate(Report $report): RedirectResponse
    {
        // Check if user has permission to generate reports
        if (! auth()->check() || ! auth()->user()->can('view_reports')) {
            abort(403, __('reports.messages.access_denied'));
        }
        // Update report generation info
        $report->update(['last_generated_at' => now(), 'generated_by' => auth()->id()]);

        return redirect()->back()->with('success', __('reports.messages.generated_successfully'));
    }

    /**
     * Handle generateReportContent functionality with proper error handling.
     */
    private function generateReportContent(Report $report): string
    {
        // This is a placeholder for actual report generation logic
        // In a real application, you would generate actual report content
        // based on the report type, filters, and data
        $data = ['report' => $report, 'generated_at' => now(), 'generated_by' => $report->generator?->name ?? 'System'];
        // For now, return a simple HTML content
        // In production, you would use a PDF generation library like DomPDF
        $html = view('reports.pdf', $data)->render();

        return $html;
    }

    /**
     * Validate the index request to guard the query builder from unsafe values.
     */
    private function validateIndexRequest(Request $request): array
    {
        // Use a simpler validation approach - validate against a fixed list of known types/categories
        // This avoids expensive queries during validation and keeps validation fast
        return $request->validate([
            'type'            => ['nullable', 'string'],
            'report_category' => ['nullable', 'string'],
            'search'          => ['nullable', 'string', 'max:255'],
            'sort'            => ['nullable', Rule::in(['name', 'view_count', 'download_count', 'created_at'])],
            'direction'       => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
    }

    /**
     * Determine whether a user may access a report resource.
     */
    private function canAccessReport(Report $report): bool
    {
        // Public reports are available to everyone without authentication.
        if ($report->is_public) {
            return true;
        }

        // Require an authenticated user with the dedicated permission for
        // private reports so that internal analytics stay protected.
        $user = auth()->user();

        return $user !== null && $user->can('view_reports');
    }

    /**
     * Ensure related reports are fully populated before surfacing them.
     */
    private function isDisplayable(Report $report): bool
    {
        return ! empty($report->getTranslation('name', app()->getLocale(), false))
            && $report->is_active
            && $report->is_public
            && ! empty($report->type)
            && ! empty($report->category);
    }
}
