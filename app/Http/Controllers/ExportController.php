<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index(): ViewContract
    {
        $disk = Storage::disk('public');
        $dir = 'exports';
        $files = [];

        if ($disk->exists($dir)) {
            foreach ($disk->files($dir) as $path) {
                $files[] = [
                    'name' => basename($path),
                    'path' => $path,
                    'size' => $disk->size($path),
                    'url' => $disk->url($path),
                ];
            }
        }

        return view('exports.index', [
            'files' => collect($files)->sortBy('name')->values()->all(),
        ]);
    }

    public function download(string $filename): HttpResponse|StreamedResponse|RedirectResponse
    {
        $path = 'exports/'.$filename;
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return redirect()->route('exports.index')->with('error', __('File not found.'));
        }

        try {
            return Response::streamDownload(function () use ($disk, $path): void {
                echo $disk->get($path);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (FileNotFoundException) {
            return redirect()->route('exports.index')->with('error', __('File not found.'));
        }
    }
}
