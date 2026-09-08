<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Increment both the per-user and per-IP counters on failure
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->ipThrottleKey(), 120); // 2-min decay for IP-level

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->ipThrottleKey());
    }

    /**
     * Ensure neither the per-user nor per-IP limit is exceeded.
     *
     * Per-user  : 5 attempts / 60 s   (email + IP combined key)
     * Per-IP    : 20 attempts / 120 s  (catches credential stuffing across accounts)
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // ── Per-user check ──────────────────────────────────────────────
        $maxPerUser = (int) config('auth.login_max_attempts', 5);
        if (RateLimiter::tooManyAttempts($this->throttleKey(), $maxPerUser)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // ── Per-IP check ────────────────────────────────────────────────
        $maxPerIp = (int) config('auth.login_max_attempts_ip', 20);
        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), $maxPerIp)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($this->ipThrottleKey());
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts from your location. Please wait '
                    . ceil($seconds / 60) . ' minute(s) before trying again.',
            ]);
        }
    }

    /**
     * Per-user throttle key: email + IP (prevents one IP testing one account).
     */
    public function throttleKey(): string
    {
        return 'login|' . Str::transliterate(Str::lower($this->string('email'))) . '|' . $this->ip();
    }

    /**
     * Per-IP throttle key: IP only (prevents one IP testing many accounts).
     */
    public function ipThrottleKey(): string
    {
        return 'login_ip|' . $this->ip();
    }
}
