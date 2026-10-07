<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountAdminController extends Controller
{
    public function index(): View
    {
        $accounts = BankAccount::query()->ordered()->get();

        return view('admin.banks.index', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        BankAccount::query()->create($data);

        return redirect()->route('admin.banks.index')->with('status', __('Bank account added.'));
    }

    public function update(Request $request, BankAccount $bank): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $bank->update($data);

        return redirect()->route('admin.banks.index')->with('status', __('Bank account saved.'));
    }

    public function destroy(BankAccount $bank): RedirectResponse
    {
        $bank->delete();

        return redirect()->route('admin.banks.index')->with('status', __('Bank account deleted.'));
    }

    /**
     * @return array{name: string, account_name: string, account_number: string, note: ?string, sort_order: mixed}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
