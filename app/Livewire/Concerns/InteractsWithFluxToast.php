<?php

namespace App\Livewire\Concerns;

trait InteractsWithFluxToast
{
    protected function toast(
        string $text,
        string $heading = 'Success',
        string $variant = 'success',
        string $position = 'top end',
        int $duration = 4000,
    ): void {
        $payload = [
            'duration' => $duration,
            'slots' => [
                'heading' => $heading,
                'text' => $text,
            ],
            'dataset' => [
                'variant' => $variant,
                'position' => $position,
            ],
        ];

        session()->flash('flux.toast', $payload);

        $this->dispatch('toast-show', ...$payload);
    }
}
