<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentRepository;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\Payments\PaymentRequest;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = $this->paymentRepository->all();

        return Inertia::render('Payments/Index', [
            'payments' => $payments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        try {
            $this->paymentRepository->store($request);
            return redirect()->back();

        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, $paymentId)
    {
        try {
            $payment = $this->paymentRepository->findById($paymentId);
            $this->paymentRepository->update($payment, $request);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($paymentId)
    {
        try {
            $payment = $this->paymentRepository->findById($paymentId);
            $this->paymentRepository->delete($payment);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
