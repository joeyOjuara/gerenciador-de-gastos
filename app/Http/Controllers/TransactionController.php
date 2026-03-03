<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Auth::user()->transactions()
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => Auth::user()->categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id'
        ]);

        Auth::user()->transactions()->create([
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => $request->date,
            'category_id' => $request->category_id
        ]);

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Verificar se o usuário é dono da transação
        if (Auth::id() !== $transaction->user_id) {
            abort(403);
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id'
        ]);

        $transaction->update([
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => $request->date,
            'category_id' => $request->category_id
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        // Verificar se o usuário é dono da transação
        if (Auth::id() !== $transaction->user_id) {
            abort(403);
        }

        $transaction->delete();
        return redirect()->back();
    }
}
