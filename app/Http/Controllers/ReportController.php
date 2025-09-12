<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

final class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Report::query()
            ->where('is_active', true)
            ->where('is_public', true);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name->' . app()->getLocale(), 'like', "%{$search}%")
                  ->orWhere('description->' . app()->getLocale(), 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortBy, ['name', 'view_count', 'download_count', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $reports = $query->paginate(12);

        // Get filter options
        $types = Report::select('type')
            ->where('is_active', true)
            ->where('is_public', true)
            ->distinct()
            ->pluck('type')
            ->mapWithKeys(fn ($type) => [$type => __("admin.reports.types.{$type}")]);

        $categories = Report::select('category')
            ->where('is_active', true)
            ->where('is_public', true)
            ->distinct()
            ->pluck('category')
            ->mapWithKeys(fn ($category) => [$category => __("admin.reports.categories.{$category}")]);

        return view('reports.index', compact('reports', 'types', 'categories'));
    }

    public function show(Report $report): View
    {
        // Check if report is public or user has access
        if (!$report->is_public && !auth()->check()) {
            abort(403, __('reports.messages.access_denied'));
        }

        // Increment view count
        $report->incrementViewCount();

        // Get related reports
        $relatedReports = Report::where('is_active', true)
            ->where('is_public', true)
            ->where('id', '!=', $report->id)
            ->where(function ($query) use ($report) {
                $query->where('type', $report->type)
                      ->orWhere('category', $report->category);
            })
            ->limit(4)
            ->get();

        return view('reports.show', compact('report', 'relatedReports'));
    }

    public function download(Report $report): Response
    {
        // Check if report is public or user has access
        if (!$report->is_public && !auth()->check()) {
            abort(403, __('reports.messages.access_denied'));
        }

        // Increment download count
        $report->incrementDownloadCount();

        // Generate PDF or return content based on report type
        $content = $this->generateReportContent($report);
        
        $filename = Str::slug($report->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generate(Report $report): RedirectResponse
    {
        // Check if user has permission to generate reports
        if (!auth()->check()) {
            abort(403, __('reports.messages.access_denied'));
        }

        // Update report generation info
        $report->update([
            'last_generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', __('reports.messages.generated_successfully'));
    }

    private function generateReportContent(Report $report): string
    {
        // This is a placeholder for actual report generation logic
        // In a real application, you would generate actual report content
        // based on the report type, filters, and data
        
        $data = [
            'report' => $report,
            'generated_at' => now(),
            'generated_by' => $report->generator?->name ?? 'System',
        ];

        // For now, return a simple HTML content
        // In production, you would use a PDF generation library like DomPDF
        $html = view('reports.pdf', $data)->render();
        
        return $html;
    }
}

