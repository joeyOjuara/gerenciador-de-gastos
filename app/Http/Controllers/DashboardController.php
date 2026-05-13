<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Inertia\Inertia;
use App\Models\User;

class DashboardController extends Controller
{
    private const MONTHS_RANGE = 3;

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);
        $baseDate = now()->setDate($year, $month, 1)->startOfMonth();

        $totalIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $totalExpenses = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $categories = Category::where('user_id', $user->id)
            ->withSum(['transactions' => function ($q) use ($month, $year, $user) {
                $q->where('user_id', $user->id)
                  ->whereMonth('date', $month)
                  ->whereYear('date', $year)
                  ->where('type', 'expense');
            }], 'amount')->get();

        $categoriesData = [
            'labels'   => $categories->pluck('name'),
            'datasets' => [[
                'label' => 'Despesas por Categoria',
                'data'  => $categories->pluck('transactions_sum_amount'),
            ]],
        ];

        $monthlyData = $this->getMonthlyData($user, $baseDate);

        $recentExpenses = $user->transactions()
            ->with('category', 'payment')
            ->where('type', 'expense')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $recentIncomes = $user->transactions()
            ->with('category', 'payment')
            ->where('type', 'income')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalIncome'      => $totalIncome,
            'totalExpenses'    => $totalExpenses,
            'categoriesData'   => $categoriesData,
            'monthlyData'      => $monthlyData,
            'recentExpenses'   => $recentExpenses,
            'recentIncomes'    => $recentIncomes,
            'currentMonth'     => $month,
            'currentYear'      => $year,
        ]);
    }

    private function getMonthlyData(User $user, Carbon $baseDate): array
    {
        $startDate = $baseDate->copy()->subMonths(self::MONTHS_RANGE)->startOfMonth();
        $endDate = $baseDate->copy()->addMonths(self::MONTHS_RANGE)->endOfMonth();

        $transactions = $user->transactions()
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($transaction) => Carbon::parse($transaction->date)->format('Y-m'));

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
