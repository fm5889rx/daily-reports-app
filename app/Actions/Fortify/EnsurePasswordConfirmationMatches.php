<?php

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordConfirmationMatches
{
    public function handle(Request $request, Closure $next): mixed
    {
        $request->validate([
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'password_confirmation.required' => 'パスワード確認を入力してください。',
            'password_confirmation.same' => 'パスワードとパスワード確認が一致しません。',
        ]);

        return $next($request);
    }
}
