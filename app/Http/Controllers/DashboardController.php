<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Transaction;
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
        $categoriesData = Category::withSum(['transactions' => function ($query) use ($now) {
                $query->whereMonth('date', $now->month)
                    ->whereYear('date', $now->year);
            }], 'amount')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'amount' => $category->transactions_sum_amount
                ];
            });

        // Últimas transações (10 mais recentes)
        $recentTransactions = $user->transactions()
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('Dashboard',[
            'totalExpenses' => $monthlyTotal,
            'categoriesData' => $categoriesData,
            'transactions' => $recentTransactions,
            'totalIncome' => 1750
        ]);
    }
}
