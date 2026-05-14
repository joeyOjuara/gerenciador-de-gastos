<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\CreditCard;
use App\Services\CreditCardInvoiceService;
use Inertia\Inertia;
use App\Models\User;

class DashboardController extends Controller
{
    private const MONTHS_RANGE = 3;

    public function __construct(private readonly CreditCardInvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);
        $accountId = $request->input('account_id');
        $baseDate = now()->setDate($year, $month, 1)->startOfMonth();

        $transactionsQuery = $user->transactions()
            ->where('is_invoice_payment', false)
            ->when($accountId, fn($query) => $query->where('account_id', $accountId));

        $totalIncome = (float) (clone $transactionsQuery)
            ->where('type', 'income')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $totalExpenses = (float) (clone $transactionsQuery)
            ->where('type', 'expense')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $accounts = $user->accounts()->orderBy('name')->get();
        $realBalance = (float) $accounts->sum('current_balance');
        $unpaidInvoicesTotal = (float) $user->creditCards()
            ->with(['invoices' => fn($query) => $query->whereIn('status', ['open', 'closed'])->withSum('transactions', 'amount')])
            ->get()
            ->flatMap->invoices
            ->sum('transactions_sum_amount');
        $projectedBalance = $realBalance - $unpaidInvoicesTotal;

        $categories = Category::where('user_id', $user->id)
            ->withSum(['transactions' => function ($q) use ($month, $year, $user, $accountId) {
                $q->where('user_id', $user->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('type', 'expense')
                    ->where('is_invoice_payment', false)
                    ->when($accountId, fn($query) => $query->where('account_id', $accountId));
            }], 'amount')
            ->get()
            ->filter(fn($category) => (float) $category->transactions_sum_amount > 0)
            ->values();

        $categoriesData = [
            'labels'   => $categories->pluck('name'),
            'datasets' => [[
                'label' => 'Despesas por Categoria',
                'data'  => $categories->pluck('transactions_sum_amount'),
            ]],
        ];

        $monthlyData = $this->getMonthlyData($user, $baseDate, $accountId);

        $creditCards = $user->creditCards()
            ->with(['invoices' => fn($query) => $query->whereIn('status', ['open', 'closed'])->withSum('transactions', 'amount')->orderBy('due_date')])
            ->orderBy('name')
            ->get()
            ->map(function (CreditCard $card) {
                $usedLimit = $this->invoiceService->getUsedLimit($card);
                $card->used_limit = $usedLimit;
                $card->available_limit = (float) $card->limit_amount - $usedLimit;
                return $card;
            });

        $recentExpenses = (clone $transactionsQuery)
            ->with('category', 'payment', 'account', 'creditCard', 'invoice')
            ->where('type', 'expense')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $recentIncomes = (clone $transactionsQuery)
            ->with('category', 'payment', 'account', 'creditCard', 'invoice')
            ->where('type', 'income')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalIncome'      => $totalIncome,
            'totalExpenses'    => $totalExpenses,
            'realBalance'      => $realBalance,
            'projectedBalance' => $projectedBalance,
            'unpaidInvoicesTotal' => $unpaidInvoicesTotal,
            'categoriesData'   => $categoriesData,
            'monthlyData'      => $monthlyData,
            'recentExpenses'   => $recentExpenses,
            'recentIncomes'    => $recentIncomes,
            'accounts'         => $accounts,
            'creditCards'      => $creditCards,
            'selectedAccountId' => $accountId ? (int) $accountId : null,
            'currentMonth'     => $month,
            'currentYear'      => $year,
        ]);
    }

    private function getMonthlyData(User $user, Carbon $baseDate, ?int $accountId = null): array
    {
        $startDate = $baseDate->copy()->subMonths(self::MONTHS_RANGE)->startOfMonth();
        $endDate = $baseDate->copy()->addMonths(self::MONTHS_RANGE)->endOfMonth();

        $transactions = $user->transactions()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_invoice_payment', false)
            ->when($accountId, fn($query) => $query->where('account_id', $accountId))
            ->get()
            ->groupBy(fn($transaction) => Carbon::parse($transaction->date)->format('Y-m'));

        $monthlyData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $key = $current->format('Y-m');
            $monthTransactions = $transactions->get($key, collect());

            $monthlyData[] = [
                'month'   => $current->translatedFormat('M/y'),
                'income'  => (float) $monthTransactions->where('type', 'income')->sum('amount'),
                'expense' => (float) $monthTransactions->where('type', 'expense')->sum('amount'),
            ];

            $current->addMonth();
        }

        return $monthlyData;
    }
}
