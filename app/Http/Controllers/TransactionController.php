<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Contracts\CategoryRepository;
use App\Contracts\PaymentRepository;
use App\Contracts\TransactionRepository;
use App\Http\Requests\Transactions\TransactionRequest;

class TransactionController extends Controller
{

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly PaymentRepository $paymentRepository
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Auth::user()->transactions()
            ->with('category')
            ->with('payment')
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => $this->categoryRepository->orderBy('name'),
            'payments' => $this->paymentRepository->orderBy('name')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {

        try {
            $request->merge([
                'user_id' => Auth::user()->id
            ]);

            $this->transactionRepository->store($request);
            return redirect()->back();

        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, $transactionId)
    {
        try {
            $transaction = $this->transactionRepository->findById($transactionId);
            $this->transactionRepository->update($transaction, $request);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($transactionId)
    {
        try {
            $transaction = $this->transactionRepository->findById($transactionId);
            $this->transactionRepository->delete($transaction);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
