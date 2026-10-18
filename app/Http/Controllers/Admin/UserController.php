<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Customers (storefront logins) and admin users (admin panel logins). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role') === 'admin' ? User::ROLE_ADMIN : User::ROLE_CUSTOMER;
        $q = User::query()->where('role', $role)->withCount('orders')->withSum('orders', 'total')->latest();
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
        }

        return view('admin.users.index', ['users' => $q->paginate(25)->withQueryString(), 'role' => $role]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.form', ['user' => new User([
            'role' => $request->query('role') === 'admin' ? User::ROLE_ADMIN : User::ROLE_CUSTOMER, 'is_active' => true,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::query()->create($this->validated($request));

        return redirect()->route('admin.users.index', ['role' => $user->role])->with('ok', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'orders' => $user->orders()->latest()->limit(20)->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        if ($user->is($request->user()) && ($data['role'] !== User::ROLE_ADMIN || ! $data['is_active'])) {
            return back()->withErrors(['role' => 'You cannot remove your own admin access.']);
        }
        if ($data['role'] !== $user->role || ! $data['is_active']) {
            $user->tokens()->delete(); // sign them out of the storefront
        }
        $user->update($data);

        return redirect()->route('admin.users.index', ['role' => $user->role])->with('ok', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        $user->tokens()->delete();
        $user->delete(); // orders are kept, detached from the account

        return back()->with('ok', 'User deleted.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
            'is_active' => ['boolean'],
            'password' => [$user ? 'nullable' : 'required', Password::min(8)],
        ], ['phone.regex' => 'Enter a valid 10-digit mobile number.']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
