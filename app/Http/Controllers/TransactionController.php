<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Vcard;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use DateTime;
use Illuminate\Support\Facades\Hash;

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

    public function store(TransactionRequest $request)
    {
        $validRequest = $request->validated();
        $vcard = VCard::find($validRequest['vcard']);

        if (!Hash::check($validRequest['confirmation_code'], $vcard->confirmation_code)) {
            return response()->json([
                'errors' => [
                    'confirmation_code' => [
                        'The confirmation code is incorrect'
                    ]
                ]
            ], 422);
        }

        if ($vcard->balance < $validRequest['value']) {
            return response()->json([
                'errors' => [
                    'value' => [
                        'Sorry, you do not have enough balance to make this transaction'
                    ]
                ]
            ], 422);
        }

        $transaction = DB::transaction(function () use ($validRequest, $vcard) {
            $utcDatetimeNow = new DateTime('now', new \DateTimeZone('UTC'));

            $transaction = new Transaction();
            $transaction->vcard = $vcard->phone_number;
            $transaction->date = $utcDatetimeNow->format('Y-m-d');
            $transaction->datetime = $utcDatetimeNow;
            $transaction->type = 'D';
            $transaction->value = $validRequest['value'];
            $transaction->payment_type = 'VCARD';
            $transaction->payment_reference = $validRequest['pair_vcard'];
            $transaction->old_balance = $vcard->balance;
            $transaction->new_balance = $vcard->balance - $validRequest['value'];
            $transaction->pair_vcard = $validRequest['pair_vcard'];
            $transaction->save();

            $pairVCard = VCard::find($validRequest['pair_vcard']);

            $pairTransaction = new Transaction();
            $pairTransaction->vcard = $validRequest['pair_vcard'];
            $pairTransaction->pair_vcard = $vcard->phone_number;
            $pairTransaction->date = $utcDatetimeNow->format('Y-m-d');
            $pairTransaction->datetime = $utcDatetimeNow;
            $pairTransaction->type = 'C';
            $pairTransaction->value = $validRequest['value'];
            $pairTransaction->payment_type = 'VCARD';
            $pairTransaction->payment_reference = $vcard->phone_number;
            $pairTransaction->old_balance = $pairVCard->balance;
            $pairTransaction->new_balance = $pairVCard->balance + $validRequest['value'];
            $pairTransaction->save();

            $pairVCard->balance = $pairTransaction->new_balance;
            $pairVCard->save();

            $vcard->balance = $transaction->new_balance;
            $vcard->save();

            //Auto Saving Piggy Bank:
            $totalBalance = $transaction->new_balance;
            $valorTransacao = $validRequest['value'];

            $centimos = $valorTransacao - floor($valorTransacao);
            // Calculate the amount left until the next integer
            $decimasSupostas = 1 - $centimos;
            // Arredondar se necessario (limitacoes hardware):
            $decimasSupostas = round($decimasSupostas * 100) / 100;

            if($totalBalance >= $decimasSupostas){
                //$vcard->balance = $vcard->balance - $decimasSupostas; //Nao  porque balance é o available balance + piggy bank balance
                $vcard->piggy_bank_balance = $vcard->piggy_bank_balance + $decimasSupostas;
                $vcard->save();
            }


            return $transaction;
        });

        return $transaction;
    }
}
