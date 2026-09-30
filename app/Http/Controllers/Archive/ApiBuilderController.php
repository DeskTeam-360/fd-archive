<?php

namespace App\Http\Controllers\Archive;

use App\Http\Controllers\Api\TicketsController as ApiTicketsController;
use App\Http\Controllers\Controller;
use App\Models\FdAgent;
use App\Models\FdCompany;
use App\Models\FdTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiBuilderController extends Controller
{
    public function index()
    {
        $allTags = Cache::remember('fd_all_tags', 3600, fn () =>
            FdTicket::whereNotNull('tags')->where('tags', '!=', '[]')
                ->pluck('tags')->flatten()->filter()->unique()->sort()->values()->toArray()
        );

        return view('archive.api-builder', [
            'agents'        => FdAgent::orderBy('name')->get(['fd_id', 'name']),
            'companiesList' => FdCompany::orderBy('name')->get(['fd_id', 'name']),
            'typeOptions'   => FdTicket::whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
            'statusMap'     => FdTicket::STATUS_MAP,
            'priorityMap'   => FdTicket::PRIORITY_MAP,
            'sourceMap'     => [1 => 'Email', 2 => 'Portal', 3 => 'Phone', 7 => 'Chat'],
            'allTags'       => $allTags,
            'apiBase'       => url('/api/v1'),
            'keyConfigured' => (string) config('services.archive_api.key') !== '',
        ]);
    }

    /** Runs the same query as the public API, authorised by the dashboard session so the key never reaches the browser. */
    public function preview(Request $request, ApiTicketsController $api)
    {
        if ($request->filled('ticket_id')) {
            return $api->show((int) $request->input('ticket_id'));
        }

        return $api->index($request);
    }

    public function docs(Request $request)
    {
        $markdown = self::renderDocs();

        return response($markdown, 200, [
            'Content-Type'        => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline') . '; filename="fd-archive-api.md"',
        ]);
    }

    public static function renderDocs(): string
    {
        return str_replace('{{BASE_URL}}', url('/'), (string) file_get_contents(base_path('docs/ARCHIVE_API.md')));
    }
}
