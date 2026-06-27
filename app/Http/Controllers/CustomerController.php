<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Support\AccessScope;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = AccessScope::customers(Customer::withCount(['rentals' => fn ($q) => AccessScope::rentals($q)]))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('full_name')->paginate(20)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        Customer::create($this->validated($request) + ['location_id' => AccessScope::locationId()]);
        return to_route('customers.index')->with('success', 'Клиент добавлен');
    }

    public function show(Customer $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->load(['rentals' => fn ($q) => $q->with('bike')->latest('started_at')]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeCustomer($customer);
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->update($this->validated($request));
        return to_route('customers.show', $customer)->with('success', 'Карточка клиента обновлена');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->update(['is_blocked' => true]);
        return back()->with('success', 'Клиент отмечен как заблокированный');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'is_blocked' => ['boolean'],
        ]);
    }

    private function authorizeCustomer(Customer $customer): void
    {
        $id = AccessScope::locationId();
        abort_if($id && $customer->location_id !== $id, 403);
    }
}
