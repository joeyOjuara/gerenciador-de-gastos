<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Category;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Get dashboard data
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Total de gastos no mês atual
        $monthlyTotal = $user->transactions()
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        // Gastos por categoria no mês atual
        $categories = Category::withSum(['transactions' => function ($query) use ($now) {
                $query->whereMonth('date', $now->month)
                    ->whereYear('date', $now->year);
            }], 'amount')
            ->get();

        $categoriesData = [
            'labels' => $categories->pluck('name'),
            'datasets' => [
                [
                    'label' => 'Despesas por Categoria',
                    'data' => $categories->pluck('transactions_sum_amount'),
                ]
            ]
        ];

        // Últimas transações (10 mais recentes)
        $recentTransactions = $user->transactions()
            ->with('category')
            ->with('payment')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('Dashboard',[
            'totalExpenses' => floatval($monthlyTotal),
            'categoriesData' => $categoriesData,
            'transactions' => $recentTransactions,
            'totalIncome' => 4200.15
        ]);
    }
}
