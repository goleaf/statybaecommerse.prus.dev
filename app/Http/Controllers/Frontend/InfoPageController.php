<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\Frontend\InfoPages;
use Illuminate\View\View;

final class InfoPageController extends Controller
{
    public function show(string $page): View
    {
        abort_unless(in_array($page, InfoPages::staticPageKeys(), true), 404);

        $pageData = InfoPages::get($page);

        abort_unless($pageData !== null, 404);

        return view('frontend.info.show', [
            'page'         => $pageData,
            'pageKey'      => $page,
            'relatedPages' => InfoPages::resolveRelatedPages($pageData['related_pages'] ?? []),
            'actions'      => InfoPages::resolveActions($pageData['actions'] ?? []),
        ]);
    }
}
