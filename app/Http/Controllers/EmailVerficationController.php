<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EmailVerficationController extends Controller
{

    public function verfiy(Request $request, $id, $hash)

    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }
        $user->markEmailAsVerified();
        $user->isVerified = true;
        $user->save();
        return response()->json(['message' => 'Email verified successfully.']);
    }
}
