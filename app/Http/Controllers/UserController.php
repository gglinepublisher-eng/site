<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::with('location.city')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('users.form', ['user' => new User(), 'locations' => $this->locations()]);
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));
        return to_route('users.index')->with('success', 'Учетная запись создана');
    }

    public function edit(User $user)
    {
        return view('users.form', ['user' => $user, 'locations' => $this->locations()]);
    }

    public function update(Request $request, User $user)
    {
        $user->update($this->validated($request, $user));
        return to_route('users.index')->with('success', 'Учетная запись обновлена');
    }

    public function destroy(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'Нельзя удалить текущую учетную запись.');
        $user->delete();
        return back()->with('success', 'Учетная запись удалена');
    }

    private function locations()
    {
        return Location::with('city')->where('is_active', true)->orderBy('name')->get();
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'location'])],
            'location_id' => ['nullable', 'required_if:role,location', 'exists:locations,id'],
            'password' => [$user ? 'nullable' : 'required', 'nullable', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ]);
        if ($data['role'] === 'admin') {
            $data['location_id'] = null;
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }
}
