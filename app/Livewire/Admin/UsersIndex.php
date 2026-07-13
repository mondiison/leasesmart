<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function render()
    {
        return view('livewire.admin.users-index', [
            'users' => User::query()->with(['roles', 'landlordProfile', 'caretakerProfile', 'tenantProfile'])->latest()->paginate(12),
        ])->layout('components.layouts.app');
    }
}
