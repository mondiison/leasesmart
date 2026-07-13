<?php

namespace App\Livewire\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RolesIndex extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    public function render()
    {
        return view('livewire.admin.roles-index', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
