<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $formFields = $request->validate(
            [
                'first_name' => ['required', 'min:3'],
                'last_name' => ['required', 'min:3'],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email'),
                    'regex:/^[0-9][a-zA-Z0-9._%+-]*@students\.[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                "isVerified" => "boolean",
                "isActive" => "nullable|boolean",

            ],
            [
                'email.regex' => 'The email must be a student email.',
            ]
        );


        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('images', 'public');
            $formFields['profile_picture'] = $profilePicturePath;
        } else {
            $formFields['profile_picture'] = null;
        }


        $formFields['Username'] = $formFields['first_name'] . ' ' . $formFields['last_name'];


        $formFields['password'] = bcrypt($formFields['password']);


        $user = User::create($formFields);

        // $token = $user->createToken('main')->plainTextToken;



        event(new Registered($user));

        return response()->json([
            'message' => 'Verfification Email was sent',

        ]);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $userData = $user->toArray();
            $userData['role'] = ['Role_name' => $user->role ? $user->role->Role_name : null];

            $token = $user->createToken('main')->plainTextToken;
            if ($user->isVerified) {

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $userData,
                ]);
            } else {
                event(new Registered($user));

                return response()->json([
                    'message' => 'please verfiy email to login',

                ]);
            }
        }

        return response()->json([
            'error' => 'Invalid credentials',
        ], 401);
    }
}
