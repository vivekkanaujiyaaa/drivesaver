<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/drive'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent select_account'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('google')->user();

        Auth::login($this->findOrCreate($user));

        return redirect('/home');
    }

    /**
     * Deauthorize from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function deauthorize()
    {
        $user = Socialite::driver('google')->deauthorize();

        auth()->user()->update([
            'token' => ''
        ]);

        redierct('/home');
    }

    /**
     * Find or create a new user.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function findOrCreate($user)
    {
        $dbUser = User::where('email', $user->email)->first();

        if ($dbUser) {
            $dbUser->update([
                'token' => $user->token,
                'refresh_token' => $user->refreshToken,
            ]);

            return $dbUser;
        }

        return User::create([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'token' => $user->token,
            'refresh_token' => $user->refreshToken,
            'avatar' => $user->getAvatar(),
        ]);
    }
}
