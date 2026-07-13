<?php

use App\Actions\Users\UpdateCurrentUserProfileAction;
use App\Http\Requests\Settings\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $bio = '';

    #[Validate('nullable|image|max:2048')]
    public $avatar;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
        $this->bio = Auth::user()->bio ?? '';
    }

    public function updateProfileInformation(UpdateCurrentUserProfileAction $updateProfile): void
    {
        $request = UpdateProfileRequest::create('/settings/profile', 'PATCH', [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'bio' => $this->bio,
        ]);
        $request->setUserResolver(fn () => Auth::user());

        if ($this->avatar !== null) {
            $request->files->set('avatar', $this->avatar);
        }

        $validated = validator(
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'bio' => $this->bio,
                'avatar' => $this->avatar,
            ],
            $request->rules(),
        )->validate();

        $updateProfile->execute(Auth::user(), $validated, $this->avatar);

        $this->reset('avatar');
        $this->dispatch('profile-updated', name: Auth::user()->fresh()->name);
    }

    public function removeAvatar(): void
    {
        $this->avatar?->delete();
        $this->avatar = null;
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Profile" subheading="Update your identity details, avatar, phone, and personal information">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="flex items-center gap-4 rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-zinc-900/60">
                <div class="flex size-16 items-center justify-center overflow-hidden rounded-2xl bg-zinc-200 text-lg font-semibold text-zinc-700 dark:bg-white/10 dark:text-white">
                    @if (auth()->user()->avatarUrl())
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="h-full w-full object-cover">
                    @else
                        {{ auth()->user()->initials() }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <flux:file-upload wire:model="avatar" label="Avatar" description="Upload a square image up to 2MB.">
                        <flux:file-upload.dropzone
                            heading="Drop avatar here or click to browse"
                            text="JPG, PNG up to 2MB"
                            with-progress
                            inline
                        />
                    </flux:file-upload>

                    @if ($avatar)
                        <div class="mt-3">
                            <flux:file-item
                                :heading="$avatar->getClientOriginalName()"
                                :image="$avatar->temporaryUrl()"
                                :size="$avatar->getSize()"
                            >
                                <x-slot name="actions">
                                    <flux:file-item.remove wire:click="removeAvatar" aria-label="Remove {{ $avatar->getClientOriginalName() }}" />
                                </x-slot>
                            </flux:file-item>
                        </div>
                    @endif
                </div>
            </div>

            <flux:input wire:model="name" label="Name" type="text" name="name" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" label="Email" type="email" name="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                            {{ __('Your email address is unverified.') }}

                            <button
                                wire:click.prevent="resendVerificationNotification"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-300 dark:hover:text-white"
                            >
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <flux:input wire:model="phone" label="Phone" type="text" name="phone" autocomplete="tel" />

            <div>
                <flux:textarea wire:model="bio" label="Personal info" rows="5" placeholder="Tell LeaseSmart teammates a little about this account." />
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
