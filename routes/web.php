<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/transactions/{transaction}/print', function (Transaction $transaction) {
        $transaction->load(['transactionDetails.product', 'user']);
        return view('print.receipt', compact('transaction'));
    })->name('transactions.print');
});
