<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Vcard;

class TransactionController extends Controller
{
    public function getVCardTransactions(Vcard $vcard)
    {
        return TransactionResource::collection($vcard->transactions()->orderBy('date', 'desc')->get());
    }

    public function getLatestVCardTransaction(Vcard $vcard)
    {
        $transaction = $vcard->transactions()->orderBy('date', 'desc')->first();

        if ($transaction == null) {
            //return laravel error
            return response()->json(
                [
                    'message' => 'No transactions found',
                    'errors' => [
                        'No transactions found'
                    ]
                ], 422);
        }

        return new TransactionResource($transaction);
    }
}
