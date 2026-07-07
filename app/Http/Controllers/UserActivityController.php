<?php

namespace App\Http\Controllers;

use App\Models\DocumentDownloadLog;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserActivityController extends Controller
{
    public function __invoke(Request $request): View
    {
        $downloads = DocumentDownloadLog::with(['document.type'])
            ->where('user_id', $request->user()->id)
            ->latest('downloaded_at')
            ->paginate(15);

        $availableDocumentIds = Document::visibleFor($request->user())
            ->whereIn('id', $downloads->getCollection()->pluck('document_id'))
            ->pluck('id');

        return view('account.activity', [
            'downloads' => $downloads,
            'availableDocumentIds' => $availableDocumentIds,
        ]);
    }
}
