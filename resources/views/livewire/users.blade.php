<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Users</h1>
        <button wire:click="openCreate"
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
            + Add User
        </button>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-700/50 border-b border-neutral-200 dark:border-neutral-700">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600 dark:text-neutral-300">Name</th>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600 dark:text-neutral-300">Email</th>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600 dark:text-neutral-300">Joined</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                @foreach ($users as $user)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/30">
                    <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-100">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-neutral-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEdit({{ $user->id }})"
                            class="text-xs text-blue-500 hover:text-blue-700 transition-colors">Edit</button>
                        @if($user->id !== auth()->id())
                        <button wire:click="delete({{ $user->id }})"
                            wire:confirm="Delete {{ $user->name }}?"
                            class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($users->isEmpty())
        <p class="text-center text-neutral-400 py-10 text-sm">No users yet.</p>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-base font-semibold text-neutral-800 dark:text-neutral-100 mb-5">
                {{ $editingId ? 'Edit User' : 'Add User' }}
            </h2>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Name</label>
                    <input wire:model="name" type="text" class="w-full border border-neutral-300 dark:border-neutral-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-purple-500" />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full border border-neutral-300 dark:border-neutral-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-purple-500" />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                        Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}
                    </label>
                    <input wire:model="password" type="password" class="w-full border border-neutral-300 dark:border-neutral-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-purple-500" />
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-neutral-500 hover:text-neutral-700 transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ $editingId ? 'Save Changes' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
