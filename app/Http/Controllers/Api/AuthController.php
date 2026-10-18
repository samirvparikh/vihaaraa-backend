<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/** Storefront customer accounts. Admins sign in through the admin panel instead. */
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], ['phone.regex' => 'Enter a valid 10-digit mobile number.']);

        $user = User::query()->create($data + ['role' => User::ROLE_CUSTOMER]);

        return $this->issueToken($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }
        if ($user->isAdmin()) {
            throw ValidationException::withMessages(['email' => 'Admin accounts sign in from the admin panel.']);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'This account has been disabled. Please contact support.']);
        }

        return $this->issueToken($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->present($request->user())]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
        ], ['phone.regex' => 'Enter a valid 10-digit mobile number.']);
        $user->update($data);

        return response()->json(['user' => $this->present($user), 'message' => 'Profile updated.']);
    }

    public function password(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $request->user()->update(['password' => $data['password']]);

        return response()->json(['message' => 'Password changed.']);
    }

    private function issueToken(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'token' => $user->createToken('storefront')->plainTextToken,
            'user' => $this->present($user),
        ], $status);
    }

    private function present(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone];
    }
}
