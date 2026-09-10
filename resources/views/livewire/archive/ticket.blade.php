<div class="space-y-5">

    {{-- Ticket header --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 space-y-3">
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-lg font-bold leading-snug">{{ $ticket->subject }}</h1>
            <div class="shrink-0 flex flex-col items-end gap-1">
                @php $color = match($ticket->status) { 2=>'bg-blue-100 text-blue-700', 3=>'bg-yellow-100 text-yellow-700', 4,5=>'bg-green-100 text-green-700', default=>'bg-neutral-100 text-neutral-600' }; @endphp
                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $color }}">{{ $ticket->status_label }}</span>
                <span class="text-xs text-neutral-400">{{ $ticket->priority_label }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Ticket #</div>
                <div class="font-mono">#{{ $ticket->fd_id }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Company</div>
                <div>
                    @if($ticket->company)
                        <a href="/archive/companies/{{ $ticket->company->fd_id }}" class="text-purple-600 hover:underline">{{ $ticket->company->name }}</a>
                    @else
                        <span class="text-neutral-400">—</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Requester</div>
                <div>
                    @if($ticket->requester)
                        <a href="/archive/contacts/{{ $ticket->requester->fd_id }}" class="text-purple-600 hover:underline">{{ $ticket->requester->name }}</a>
                        <div class="text-xs text-neutral-400">{{ $ticket->requester->email }}</div>
                    @elseif($ticket->requesterAgent)
                        <span class="font-medium">{{ $ticket->requesterAgent->name }}</span>
                        <span class="ml-1 text-xs bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 px-1.5 py-0.5 rounded">Agent</span>
                        <div class="text-xs text-neutral-400">{{ $ticket->requesterAgent->email }}</div>
                    @else
                        <span class="text-neutral-400">ID: {{ $ticket->fd_requester_id ?? '—' }}</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Responder</div>
                <div>
                    @if($ticket->responderAgent)
                        <span class="font-medium">{{ $ticket->responderAgent->name }}</span>
                        <span class="ml-1 text-xs bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 px-1.5 py-0.5 rounded">Agent</span>
                    @else
                        <span class="text-neutral-400">—</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Type</div>
                <div>{{ $ticket->type ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Created</div>
                <div>{{ $ticket->fd_created_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Updated</div>
                <div>{{ $ticket->fd_updated_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
        </div>

        @if($ticket->tags && count($ticket->tags))
        <div class="flex flex-wrap gap-1 pt-1">
            @foreach($ticket->tags as $tag)
                <span class="text-xs bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 px-2 py-0.5 rounded-full">{{ $tag }}</span>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Description --}}
    @if($ticket->description)
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h2 class="text-sm font-semibold text-neutral-500 uppercase tracking-wide mb-3">Description</h2>
        <div class="prose prose-sm dark:prose-invert max-w-none text-sm overflow-x-auto">
            {!! $ticket->description !!}
        </div>
    </div>
    @endif

    {{-- Comments --}}
    <div>
        <h2 class="font-semibold text-base mb-3">Comments ({{ $ticket->comments->count() }})</h2>
        <div class="space-y-3">
            @forelse($ticket->comments as $comment)
            <div class="rounded-xl border {{ $comment->incoming ? 'border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10' : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800' }} p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        @if($comment->incoming)
                            <span class="text-xs bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 px-2 py-0.5 rounded-full font-medium">Incoming</span>
                        @else
                            <span class="text-xs bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 px-2 py-0.5 rounded-full font-medium">Reply</span>
                        @endif
                        @if($comment->private)
                            <span class="text-xs bg-yellow-100 text-yellow-600 px-2 py-0.5 rounded-full font-medium">Private</span>
                        @endif
                        <span class="text-xs text-neutral-500">{{ $comment->from_email ?: ('User #'.$comment->fd_user_id) }}</span>
                    </div>
                    <span class="text-xs text-neutral-400">{{ $comment->fd_created_at?->format('Y-m-d H:i') }}</span>
                </div>

                <div class="prose prose-sm dark:prose-invert max-w-none text-sm overflow-x-auto">
                    {!! $comment->body !!}
                </div>

                @if($comment->attachments && count($comment->attachments))
                <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-700 flex flex-wrap gap-2">
                    @foreach($comment->attachments as $att)
                        @if($att['url'])
                        <a href="{{ $att['url'] }}" target="_blank"
                            class="text-xs bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 px-3 py-1.5 rounded-lg flex items-center gap-1 transition">
                            📎 {{ $att['name'] }}
                            @if(isset($att['size']))
                                <span class="text-neutral-400">({{ round($att['size']/1024) }}KB)</span>
                            @endif
                        </a>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-neutral-400 text-sm py-6">No comments</div>
            @endforelse
        </div>
    </div>

</div>
