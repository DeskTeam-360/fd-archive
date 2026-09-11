<?php

namespace App\Http\Controllers\Archive;

use App\Http\Controllers\Controller;
use App\Models\FdAgent;
use App\Models\FdCompany;
use App\Models\FdTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TicketsController extends Controller
{
    public function index(Request $request)
    {
        $q          = trim($request->input('search', ''));
        $agents     = array_filter((array) $request->input('agents', []));
        $statuses   = array_filter((array) $request->input('statuses', []));
        $priorities = array_filter((array) $request->input('priorities', []));
        $types      = array_filter((array) $request->input('types', []));
        $sources    = array_filter((array) $request->input('sources', []));
        $companies  = array_filter((array) $request->input('companies', []));
        $tags       = array_filter((array) $request->input('tags', []));
        $dateFrom   = $request->input('date_from', '');
        $dateTo     = $request->input('date_to', '');

        $tickets = FdTicket::query()
            ->when($q,          fn ($x) => $x->where(fn ($x) => $x
                ->where('subject', 'like', "%{$q}%")
                ->orWhere('description_text', 'like', "%{$q}%")
                ->orWhereHas('comments', fn ($c) => $c->where('body_text', 'like', "%{$q}%"))))
            ->when($agents,     fn ($x) => $x->whereIn('fd_responder_id', $agents))
            ->when($statuses,   fn ($x) => $x->whereIn('status', $statuses))
            ->when($priorities, fn ($x) => $x->whereIn('priority', $priorities))
            ->when($types,      fn ($x) => $x->whereIn('type', $types))
            ->when($sources,    fn ($x) => $x->whereIn('source', $sources))
            ->when($companies,  fn ($x) => $x->whereIn('fd_company_id', $companies))
            ->when($tags, fn ($x) => $x->where(fn ($q) => collect($tags)
                ->each(fn ($tag) => $q->orWhereJsonContains('tags', $tag))))
            ->when($dateFrom,   fn ($x) => $x->where('fd_created_at', '>=', $dateFrom))
            ->when($dateTo,     fn ($x) => $x->where('fd_created_at', '<=', $dateTo.' 23:59:59'))
            ->with('company:fd_id,name')
            ->withCount('comments')
            ->orderByDesc('fd_created_at')
            ->paginate(25)
            ->appends($request->query());

        $allTags = Cache::remember('fd_all_tags', 3600, fn () =>
            FdTicket::whereNotNull('tags')->where('tags', '!=', '[]')
                ->pluck('tags')->flatten()->filter()->unique()->sort()->values()->toArray()
        );

        return view('archive.tickets', [
            'tickets'       => $tickets,
            'agents'        => FdAgent::orderBy('name')->get(['fd_id', 'name']),
            'companiesList' => FdCompany::orderBy('name')->get(['fd_id', 'name']),
            'typeOptions'   => FdTicket::whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
            'statusMap'     => FdTicket::STATUS_MAP,
            'priorityMap'   => FdTicket::PRIORITY_MAP,
            'sourceMap'     => [1 => 'Email', 2 => 'Portal', 3 => 'Phone', 7 => 'Chat'],
            'allTags'       => $allTags,
            // current filter values for pre-selecting form fields
            'fSearch'     => $q,
            'fAgents'     => $agents,
            'fStatuses'   => $statuses,
            'fPriorities' => $priorities,
            'fTypes'      => $types,
            'fSources'    => $sources,
            'fCompanies'  => $companies,
            'fTags'       => $tags,
            'fDateFrom'   => $dateFrom,
            'fDateTo'     => $dateTo,
        ]);
    }
}
