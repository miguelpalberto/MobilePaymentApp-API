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
}
