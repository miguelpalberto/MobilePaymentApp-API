<?php

namespace App\Http\Controllers;

use App\Models\Vcard;

class TransactionController extends Controller
{
    public function getVCardTransactions(Vcard $vcard)
    {
        return $vcard->transactions;
    }
}
