<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditCards\CreditCardRequest;
use App\Http\Requests\CreditCards\PayInvoiceRequest;
use App\Models\Account;
use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Services\CreditCardInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreditCardController extends Controller
{
    public function __construct(private readonly CreditCardInvoiceService $invoiceService) {}

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $creditCards = $user->creditCards()
            ->with(['invoices' => fn($query) => $query->withSum('transactions', 'amount')->orderByDesc('reference_year')->orderByDesc('reference_month')])
            ->orderBy('name')
            ->get()
            ->map(function (CreditCard $card) {
                $usedLimit = $this->invoiceService->getUsedLimit($card);
                $card->used_limit = $usedLimit;
                $card->available_limit = (float) $card->limit_amount - $usedLimit;
                return $card;
            });

        return Inertia::render('CreditCards/Index', [
            'creditCards' => $creditCards,
            'accounts' => $user->accounts()->orderBy('name')->get(),
        ]);
    }

    public function store(CreditCardRequest $request): RedirectResponse
    {
        Auth::user()->creditCards()->create($request->validated());

        return redirect()->back();
    }

    public function update(CreditCardRequest $request, int $id): RedirectResponse
    {
        Auth::user()->creditCards()->findOrFail($id)->update($request->validated());

        return redirect()->back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $card = Auth::user()->creditCards()->withCount('transactions')->findOrFail($id);

        if ($card->transactions_count > 0) {
            return redirect()->back()->withErrors('Não é possível excluir um cartão com transações vinculadas.');
        }

        $card->delete();

        return redirect()->back();
    }

    public function payInvoice(PayInvoiceRequest $request, int $invoiceId): RedirectResponse
    {
        $invoice = CreditCardInvoice::where('user_id', Auth::id())->findOrFail($invoiceId);
        $account = Account::where('user_id', Auth::id())->findOrFail($request->account_id);

        try {
            $this->invoiceService->payInvoice($invoice, $account, $request->payment_date);

            return redirect()->back();
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors($exception->getMessage());
        }
    }
}
