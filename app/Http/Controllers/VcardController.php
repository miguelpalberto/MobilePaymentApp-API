<?php

namespace App\Http\Controllers;

use App\Models\Vcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Hash;
use App\Http\Resources\VcardResource;
use App\Http\Requests\StoreVcardRequest;
use App\Http\Requests\DeleteVCardRequest;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Resources\VcardContactsResource;
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

    public function exists($vcard)
    {
        return response()->json(Vcard::where('phone_number', $vcard)->exists(), 200);
    }

    public function update(Request $request, Vcard $vcard)
    {
        //$vcard->fill($request->validated());
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $vcard->update($data);

        return new VcardResource($vcard);
    }

    public function updatePiggyBankBalance(UpdatePiggyBankBalanceRequest $request, Vcard $vcard)
    {

        $request->validated();

        if ($request->piggy_bank_balance > $vcard->balance) {
            //return laravel error
            return response()->json(
                [
                    'errors' => [
                        'piggy_bank_balance' => ['Cannot store more money than the Total Balance in the Piggy Bank Vault']
                    ]
                ],
                422
            );
        }

        $vcard->piggy_bank_balance = $request->piggy_bank_balance;
        $vcard->save();
        return new VcardResource($vcard);
    }


    public function toggleNotifications(Vcard $vcard)
    {

        $vcard->notifications = !$vcard->notifications;

        $vcard->save();

        return response()->json([
            'notifications' => $vcard->notifications,
        ], 200);
    }


    public function getNotificationsToggle(Vcard $vcard)
    {
        $columnValue = $vcard->notifications;

        return response()->json([
            'notifications' => $columnValue,
        ], 200);
    }


    public function getVCardContacts(Request $request)
    {
        $vcard = $request->user();
        $contacts = $request->input('contacts');
        if ($contacts == null) {
            return response()->json([
                'contacts' => []
            ], 200);
        }
        $existingVCardContacts = [];

        foreach ($contacts as $contact) {
            $existingContact = Vcard::where('phone_number', $contact)->first();
            if ($existingContact && $existingContact->deleted_at == null && $existingContact->phone_number != $vcard->id) {
                $existingVCardContacts[] = $existingContact['phone_number'];
            }
        }

        return response()->json([
            'contacts' => $existingVCardContacts
        ], 200);
    }

    public function destroy(VCard $vcard, DeleteVCardRequest $request)
    {
        $user = $request->user();

        $validRequest = $request->validated();
        //if user is not admin, check if password and confirmation code are correct
        //else if user is admin, just delete the vcard
        if ($user->user_type != 'A') {
            if (!Hash::check($validRequest['password'], $vcard->password)) {
                return response()->json([
                    'errors' => [
                        'password' => [
                            'The password is incorrect'
                        ]
                    ]
                ], 422);
            }

            if (!Hash::check($validRequest['confirmation_code'], $vcard->confirmation_code)) {
                return response()->json([
                    'errors' => [
                        'confirmation_code' => [
                            'The confirmation code is incorrect'
                        ]
                    ]
                ], 422);
            }
        }

        if ($vcard->balance > 0) {
            return response()->json([
                'errors' => [
                    'balance' => [
                        'Cannot delete vCard with positive balance'
                    ]
                ]
            ], 422);
        }

        $hasTransactions = $vcard->transactions()->exists();

        if ($hasTransactions) {
            $vcard->transactions()->delete();
            $vcard->categories()->delete();
            $vcard->delete();  // Soft delete
        } else {
            $vcard->categories()->forceDelete();
            $vcard->forceDelete();  // Hard delete
        }

        return response()->noContent();
    }
}
