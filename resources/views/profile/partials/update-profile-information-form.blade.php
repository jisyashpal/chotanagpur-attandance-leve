<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route(auth()->user()->role . '.profile.update') }}" class="mt-2" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="mb-4 text-center">
            @if (auth()->user()->getProfilePhotoUrl())
                <img src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="Profile" 
                     class="rounded-circle shadow-sm mb-2" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--admin-primary);">
            @else
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2" style="width: 100px; height: 100px; border: 3px dashed #ddd;">
                    <i class="fas fa-user-circle fa-4x text-muted"></i>
                </div>
            @endif
            <div class="mt-2">
                <label for="profile_photo" class="form-label d-block small fw-bold">Update Profile Photo</label>
                <input type="file" name="profile_photo" id="profile_photo" class="form-control form-control-sm" accept="image/*">
            </div>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-dark">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline text-sm">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2 small p-2">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>{{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small animated fadeIn">
                    <i class="fas fa-check-circle me-1"></i>{{ __('Saved successfully.') }}
                </span>
            @endif
        </div>
    </form>
</section>
