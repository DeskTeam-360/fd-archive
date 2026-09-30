<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FdAttachment;
use App\Models\FdComment;
use App\Models\FdTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    private const SOURCE_MAP = [1 => 'Email', 2 => 'Portal', 3 => 'Phone', 7 => 'Chat'];

    private const SORTABLE = [
        'id'         => 'fd_id',
        'created_at' => 'fd_created_at',
        'updated_at' => 'fd_updated_at',
        'status'     => 'status',
        'priority'   => 'priority',
    ];

    public function index(Request $request): JsonResponse
    {
        $search     = trim((string) $request->input('search', ''));
        $ids        = $this->multi($request, 'ids');
        $agents     = $this->multi($request, 'agents');
        $requesters = $this->multi($request, 'requesters');
        $statuses   = $this->multi($request, 'statuses');
        $priorities = $this->multi($request, 'priorities');
        $types      = $this->multi($request, 'types');
        $sources    = $this->multi($request, 'sources');
        $companies  = $this->multi($request, 'companies');
        $tags       = $this->multi($request, 'tags');
        $include    = $this->multi($request, 'include');

        $createdFrom = $request->input('created_from', $request->input('date_from'));
        $createdTo   = $request->input('created_to', $request->input('date_to'));
        $updatedFrom = $request->input('updated_from');
        $updatedTo   = $request->input('updated_to');

        $sortColumn = self::SORTABLE[$request->input('sort', 'created_at')] ?? 'fd_created_at';
        $sortDir    = strtolower((string) $request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage    = min(100, max(1, (int) $request->input('per_page', 25)));

        $withComments = in_array('comments', $include, true);

        $tickets = FdTicket::query()
            ->when($search !== '', fn ($x) => $x->where(fn ($x) => $x
                ->where('subject', 'like', "%{$search}%")
                ->orWhere('description_text', 'like', "%{$search}%")
                ->orWhereHas('comments', fn ($c) => $c->where('body_text', 'like', "%{$search}%"))))
            ->when($ids,        fn ($x) => $x->whereIn('fd_id', $ids))
            ->when($agents,     fn ($x) => $x->whereIn('fd_responder_id', $agents))
            ->when($requesters, fn ($x) => $x->whereIn('fd_requester_id', $requesters))
            ->when($statuses,   fn ($x) => $x->whereIn('status', $statuses))
            ->when($priorities, fn ($x) => $x->whereIn('priority', $priorities))
            ->when($types,      fn ($x) => $x->whereIn('type', $types))
            ->when($sources,    fn ($x) => $x->whereIn('source', $sources))
            ->when($companies,  fn ($x) => $x->whereIn('fd_company_id', $companies))
            ->when($tags, fn ($x) => $x->where(fn ($q) => collect($tags)
                ->each(fn ($tag) => $q->orWhereJsonContains('tags', $tag))))
            ->when($createdFrom, fn ($x) => $x->where('fd_created_at', '>=', $createdFrom))
            ->when($createdTo,   fn ($x) => $x->where('fd_created_at', '<=', $this->endOfDay($createdTo)))
            ->when($updatedFrom, fn ($x) => $x->where('fd_updated_at', '>=', $updatedFrom))
            ->when($updatedTo,   fn ($x) => $x->where('fd_updated_at', '<=', $this->endOfDay($updatedTo)))
            ->with([
                'company:fd_id,name',
                'requester:fd_id,name,email',
                'requesterAgent:fd_id,name,email',
                'responderAgent:fd_id,name,email',
            ])
            ->when($withComments, fn ($x) => $x->with(['comments' => fn ($c) => $c->orderBy('fd_created_at')]))
            ->withCount('comments')
            ->orderBy($sortColumn, $sortDir)
            ->orderBy('fd_id', $sortDir)
            ->paginate($perPage);

        $files = $withComments ? $this->filesByTicket($tickets->pluck('fd_id')->all()) : [];

        return response()->json([
            'data' => $tickets->getCollection()
                ->map(fn (FdTicket $t) => $this->ticket($t, $withComments, $files[$t->fd_id] ?? []))
                ->values(),
            'meta' => [
                'page'      => $tickets->currentPage(),
                'per_page'  => $tickets->perPage(),
                'total'     => $tickets->total(),
                'last_page' => $tickets->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $ticket = FdTicket::with([
            'company:fd_id,name',
            'requester:fd_id,name,email',
            'requesterAgent:fd_id,name,email',
            'responderAgent:fd_id,name,email',
            'comments' => fn ($c) => $c->orderBy('fd_created_at'),
        ])->withCount('comments')->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        return response()->json([
            'data' => $this->ticket($ticket, true, $this->filesByTicket([$id])[$id] ?? []),
        ]);
    }

    public function attachment(int $id)
    {
        $attachment = FdAttachment::find($id);
        $file = $attachment?->path ? storage_path('app/private/' . $attachment->path) : null;

        if (! $attachment?->downloaded_at || ! $file || ! is_file($file)) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        // Explicit Content-Type: Symfony would otherwise guess it via ext-fileinfo, which this server may lack.
        return response()->file($file, [
            'Content-Type'        => $attachment->content_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $attachment->name) . '"',
        ]);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'statuses'   => FdTicket::STATUS_MAP,
            'priorities' => FdTicket::PRIORITY_MAP,
            'sources'    => self::SOURCE_MAP,
            'types'      => FdTicket::whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
            'sort'       => array_keys(self::SORTABLE),
        ]);
    }

    /** Accepts `key=a,b`, `key[]=a&key[]=b`, or a mix of both. */
    private function multi(Request $request, string $key): array
    {
        return collect((array) $request->input($key, []))
            ->flatMap(fn ($v) => explode(',', (string) $v))
            ->map(fn ($v) => trim($v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function endOfDay(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value . ' 23:59:59' : $value;
    }

    /** Downloaded files grouped by ticket id. */
    private function filesByTicket(array $ticketIds): array
    {
        if (! $ticketIds) {
            return [];
        }

        return FdAttachment::whereIn('fd_ticket_id', $ticketIds)
            ->whereNotNull('downloaded_at')
            ->get()
            ->groupBy('fd_ticket_id')
            ->all();
    }

    private function ticket(FdTicket $t, bool $withComments, $files): array
    {
        $files = collect($files);
        $requester = $t->requester ?? $t->requesterAgent;

        $data = [
            'id'             => $t->fd_id,
            'subject'        => $t->subject,
            'status'         => $t->status,
            'status_label'   => $t->status_label,
            'priority'       => $t->priority,
            'priority_label' => $t->priority_label,
            'type'           => $t->type,
            'source'         => $t->source,
            'source_label'   => self::SOURCE_MAP[$t->source] ?? null,
            'tags'           => $t->tags ?? [],
            'company'        => $t->company ? ['id' => $t->company->fd_id, 'name' => $t->company->name] : null,
            'requester'      => $requester
                ? ['id' => $requester->fd_id, 'name' => $requester->name, 'email' => $requester->email]
                : ($t->fd_requester_id ? ['id' => $t->fd_requester_id, 'name' => null, 'email' => null] : null),
            'agent'          => $t->responderAgent
                ? ['id' => $t->responderAgent->fd_id, 'name' => $t->responderAgent->name, 'email' => $t->responderAgent->email]
                : null,
            'custom_fields'  => $t->custom_fields,
            'due_by'         => $t->due_by?->toIso8601String(),
            'created_at'     => $t->fd_created_at?->toIso8601String(),
            'updated_at'     => $t->fd_updated_at?->toIso8601String(),
            'comments_count' => $t->comments_count,
        ];

        if ($withComments) {
            $data['description']      = $t->description;
            $data['description_text'] = $t->description_text;
            $data['attachments']      = $files->whereNull('fd_comment_id')->map(fn ($f) => $this->file($f))->values();
            $data['comments']         = $t->comments->map(fn (FdComment $c) => [
                'id'          => $c->fd_id,
                'user_id'     => $c->fd_user_id,
                'incoming'    => $c->incoming,
                'private'     => $c->private,
                'from_email'  => $c->from_email,
                'to_emails'   => $c->to_emails ?? [],
                'cc_emails'   => $c->cc_emails ?? [],
                'body'        => $c->body,
                'body_text'   => $c->body_text,
                'attachments' => $files->where('fd_comment_id', $c->fd_id)->map(fn ($f) => $this->file($f))->values(),
                'created_at'  => $c->fd_created_at?->toIso8601String(),
                'updated_at'  => $c->fd_updated_at?->toIso8601String(),
            ])->values();
        }

        return $data;
    }

    private function file(FdAttachment $f): array
    {
        return [
            'id'           => $f->fd_id,
            'name'         => $f->name,
            'content_type' => $f->content_type,
            'size'         => $f->size,
            'url'          => url("/api/v1/attachments/{$f->fd_id}"),
        ];
    }
}
