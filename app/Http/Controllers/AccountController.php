<?php

namespace App\Http\Controllers;

use App\Http\Requests\Accounts\AccountRequest;
use App\Http\Requests\Accounts\TransferRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => Auth::user()->accounts()->orderBy('name')->get(),
        ]);
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        try {
            Auth::user()->accounts()->create([
                'name' => $request->name,
                'initial_balance' => $request->initial_balance,
                'current_balance' => $request->initial_balance,
            ]);

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(AccountRequest $request, int $id): RedirectResponse
    {
        try {
            $account = Auth::user()->accounts()->findOrFail($id);
            $difference = (float) $request->initial_balance - (float) $account->initial_balance;

            $account->update([
                'name' => $request->name,
                'initial_balance' => $request->initial_balance,
                'current_balance' => (float) $account->current_balance + $difference,
            ]);

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $account = Auth::user()->accounts()->withCount('transactions')->findOrFail($id);

            if ($account->transactions_count > 0) {
                return redirect()->back()->withErrors('Não é possível excluir uma conta com transações vinculadas.');
            }

            $account->delete();

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function transfer(TransferRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $fromAccount = Account::where('user_id', Auth::id())->lockForUpdate()->findOrFail($request->from_account_id);
                $toAccount = Account::where('user_id', Auth::id())->lockForUpdate()->findOrFail($request->to_account_id);
                $amount = (float) $request->amount;

                $fromAccount->decrement('current_balance', $amount);
                $toAccount->increment('current_balance', $amount);
            });

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
