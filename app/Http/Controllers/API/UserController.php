<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' =>['required','string','max:255'],
                'username' =>['required','string','max:255','unique:users'],
                'email' =>['required','string','max:255','unique:users'],
                'phone' =>['nullable','string','max:255'],
                'password' =>['required','string', new Password],
            ]);

            User::create([
                'name' =>  $request->input('name'),
                'username' => $request->username,
                'email' =>  $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Register');
        }    catch (Exception $error) {
              return ResponseFormatter::error([
                    'message' => 'Something wrong',
                    'error' => $error
              ],'User Salah', 500);
            
        }
    }

    public function login(Request $request) 
    {
         try {
             $request->validate([
            'email' => 'email|required',
            'password' => 'required',

             ]);

             $credentials = request(['email','password']);
             if(!Auth::attempt($credentials))
             {
                 return ResponseFormatter::error([
                     'messages' => 'GAtot alias gagal toal bro'
                 ],'login gagal',500);
             }

             $user = User::where('email', $request->email)->first();
            
             if (! Hash::check($request->password, $user->password, []))
             {
                    throw new \Exception('Invalid Credentials');
             }

             $tokenResult = $user->createToken('authToken')->plainTextToken;
   
             return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'login benar');

         }  catch (Exception $error) {
            return ResponseFormatter::error([
                  'message' => 'Tidak jelas',
                  'error' => $error
            ],'login Salah', 500);
         }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data profile user masuk');
    }

    public function updateprofile(Request $request)
    {

        $data  = $request->all();
        $user= Auth::user();

        $user->update($data);
        
        return ResponseFormatter::success($user, 'Profile update');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'keluar dari program');
    }
}
 