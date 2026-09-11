<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Users'])]

class Users extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|min:2')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('nullable|min:8')]
    public ?string $password = null;

    public function openCreate(): void
    {
        $this->reset('name', 'email', 'password', 'editingId');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'     => 'required|min:2',
            'email'    => 'required|email|unique:users,email' . ($this->editingId ? ",{$this->editingId}" : ''),
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
        ]);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->name  = $this->name;
            $user->email = $this->email;
            if ($this->password) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
        } else {
            User::create([
                'name'              => $this->name,
                'email'             => $this->email,
                'password'          => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);
        }

        $this->showModal = false;
        $this->reset('name', 'email', 'password', 'editingId');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            return; // can't delete self
        }
        User::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.users', [
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
