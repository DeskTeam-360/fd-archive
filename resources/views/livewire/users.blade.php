<div class="w-full space-y-4">
    <div class="flex w-full items-center justify-between mb-2">
        <h1 class="text-xl font-semibold text-neutral-800">Users</h1>
        <button wire:click="openCreate"
            style="background:#7c3aed;color:#fff;padding:8px 16px;border-radius:8px;font-size:14px;font-weight:500;border:none;cursor:pointer;">
            + Add User
        </button>
    </div>

    <div class="rounded-xl border border-neutral-200 overflow-hidden bg-white">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 border-b border-neutral-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600">Name</th>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600">Email</th>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600">Joined</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($users as $user)
                <tr class="hover:bg-neutral-50">
                    <td class="px-4 py-3 font-medium text-neutral-800">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-neutral-500">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-neutral-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openEdit({{ $user->id }})"
                            class="text-xs text-blue-500 hover:text-blue-700">Edit</button>
                        @if($user->id !== auth()->id())
                        <button wire:click="delete({{ $user->id }})"
                            wire:confirm="Delete {{ $user->name }}?"
                            class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-neutral-400">No users yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-base font-semibold text-neutral-800 mb-5">
                {{ $editingId ? 'Edit User' : 'Add User' }}
            </h2>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-600 mb-1">Name</label>
                    <input wire:model="name" type="text"
                        class="w-full border border-neutral-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-600 mb-1">Email</label>
                    <input wire:model="email" type="email"
                        class="w-full border border-neutral-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-600 mb-1">
                        Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}
                    </label>
                    <input wire:model="password" type="password"
                        class="w-full border border-neutral-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-neutral-500 hover:text-neutral-700">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg">
                        {{ $editingId ? 'Save Changes' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
