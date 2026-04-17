<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
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
    ) {}

    public function expenseIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = $user->transactions()
            ->where('type', 'expense')
            ->with('category', 'payment')
            ->orderBy('date', 'desc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_id')) {
            $query->where('payment_id', $request->payment_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', "%{$request->description}%");
        }

        return Inertia::render('Transactions/Index', [
            'transactions' => $query->paginate(20)->withQueryString(),
            'categories'   => $this->categoryRepository->orderBy('name'),
            'payments'     => $this->paymentRepository->orderBy('name'),
            'filters'      => $request->only(['category_id', 'payment_id', 'month', 'year']),
        ]);
    }

    public function incomeIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = $user->transactions()
            ->where('type', 'income')
            ->with('category', 'payment')
            ->orderBy('date', 'desc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_id')) {
            $query->where('payment_id', $request->payment_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        return Inertia::render('Incomes/Index', [
            'transactions' => $query->paginate(20)->withQueryString(),
            'categories'   => $this->categoryRepository->orderBy('name'),
            'payments'     => $this->paymentRepository->orderBy('name'),
            'filters'      => $request->only(['category_id', 'payment_id', 'month', 'year']),
        ]);
    }

    public function store(TransactionRequest $request)
    {
        try {
            $request->merge([
                'user_id' => Auth::id(),
            ]);

            for($request->number; $request->number > 0; $request->number--) {
                $this->transactionRepository->store($request);
                $request->merge([
                    'date' => Carbon::parse($request->date)->addMonth()->format('Y-m-d'),
                ]);
            }


            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

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

    public function destroyBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            Auth::user()->transactions()->whereIn('id', $ids)->delete();
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
