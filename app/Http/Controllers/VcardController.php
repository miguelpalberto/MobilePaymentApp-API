<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vcard;
use App\Http\Resources\VcardResource;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreVcardRequest;
use App\Http\Requests\UpdatePiggyBankBalanceRequest;

class VcardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VcardResource::collection(Vcard::all());
    }


    public function store(StoreVcardRequest $request)
    {
        $vcard = new Vcard();
        $vcard->fill($request->validated());
        $vcard->password = bcrypt($request->password);
        $vcard->blocked = FALSE;
        $vcard->balance = 0;
        $vcard->save();

        return new VcardResource($vcard);

    }

    public function show(Vcard $vcard)
    {
        return new VcardResource($vcard);
    }

    public function update(Request $request, Vcard $vcard)
    {
    }

    public function destroy(Vcard $vcard)
    {
    }

    public function updatePiggyBankBalance(UpdatePiggyBankBalanceRequest $request, Vcard $vcard){

        $request->validated();

        if ($request->piggy_bank_balance > $vcard->balance) {
            //return laravel error
            return response()->json(
                [
                    'errors' => [
                        'piggy_bank_balance' => ['Piggy bank balance cannot be greater than balance']
                    ]
                ], 422);
        }

        $vcard->piggy_bank_balance = $request->piggy_bank_balance;
        $vcard->save();
        return new VcardResource($vcard);
    }
}
