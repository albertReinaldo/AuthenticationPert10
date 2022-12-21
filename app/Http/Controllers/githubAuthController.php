<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class githubAuthController extends Controller
{
    public function githubRedirect(){
        return Socialite::driver('github')->redirect();
    }

    public function githubCallback(){
        try {
            $socialiteUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            return redirect('/login');
        }

        $user = User::where([
            'provider'=>'github',
            'provider_id'=>$socialiteUser->getId(),
        ])->first();

        if(!$user){
            $validator = Validator::make(
                ['email' => $socialiteUser->getEmail()],
                ['email'=> ['unique:users,email']],
                ['email.unique'=>'Couldnt log in. Maybe you used a different login method']
            );

            if($validator->fails()){
                return redirect('/login')->withErrors($validator);
            }

            $user = User::create([
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'provider' => 'github',
                'provider_id' => $socialiteUser->getId(),
                'email_verified_at' => now()
            ]);
        }

        Auth::login($user);
        return redirect('/home');

        dd($user->getName(),$user->getEmail(),$user->getId());
    }

}
