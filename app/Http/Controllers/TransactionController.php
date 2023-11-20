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
        $count = $vcard->transactions()->count();
        $transaction = null;

        if ($count > 0) {
            $transaction = new TransactionResource($vcard->transactions()->orderBy('date', 'desc')->first());
        }
        
        return response()->json([
            'latestTransaction' => $transaction,
            'total' => $count
        ], 200);
    }
}
