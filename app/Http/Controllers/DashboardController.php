<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

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

        $categories = Category::withSum(['transactions' => function ($q) use ($month, $year) {
            $q->whereMonth('date', $month)
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

        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData[] = [
                'month'   => $date->translatedFormat('M/y'),
                'income'  => (float) $user->transactions()
                    ->where('type', 'income')
                    ->whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->sum('amount'),
                'expense' => (float) $user->transactions()
                    ->where('type', 'expense')
                    ->whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->sum('amount'),
            ];
        }

        $recentTransactions = $user->transactions()
            ->with('category', 'payment')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalIncome'      => $totalIncome,
            'totalExpenses'    => $totalExpenses,
            'categoriesData'   => $categoriesData,
            'monthlyData'      => $monthlyData,
            'transactions'     => $recentTransactions,
            'currentMonth'     => $month,
            'currentYear'      => $year,
        ]);
    }
}
