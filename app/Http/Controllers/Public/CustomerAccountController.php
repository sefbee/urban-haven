<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\PhoneNumberRule;
use App\Support\PhoneNumber;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        abort_if($request->user() !== null, 409, 'You are already signed in.');

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => [
                'required',
                'email:rfc',
                'max:190',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower((string) $value)])->exists()) {
                        $fail(__('This email is already registered. Sign in to your account instead.'));
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:24', new PhoneNumberRule],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => trim($data['name']),
            'email' => Str::lower(trim($data['email'])),
            'phone' => PhoneNumber::normalize($data['phone']) ?? trim($data['phone']),
            'password' => $data['password'],
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => __('Your account is ready. Submit your enquiry to save it to your property history.'),
            'user' => ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone],
            'profile_url' => route('account.show'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        abort_if($request->user() !== null, 409, 'You are already signed in.');

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $key = 'customer-login:'.Str::lower($data['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('Too many sign-in attempts. Please try again shortly.')]);
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [Str::lower($data['email'])])->first();

        if (! $user || ! $user->is_active || $user->roles()->exists() || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages(['email' => __('These details do not match a customer account.')]);
        }

        RateLimiter::clear($key);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => __('You are signed in. Submit your enquiry to add it to your property history.'),
            'user' => ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone],
            'profile_url' => route('account.show'),
        ]);
    }

    public function show(Request $request): View
    {
        abort_unless($request->user()->roles()->doesntExist(), 404);

        $leads = $request->user()->leads()
            ->with(['property.media', 'project', 'siteVisits'])
            ->latest()
            ->paginate(10);

        return view('public.account.show', compact('leads'));
    }

    public function logout(Request $request): RedirectResponse
    {
        abort_unless($request->user()->roles()->doesntExist(), 404);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
