<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::INVALID_USER) {
            return response()->json(['message' => 'Adresse email introuvable'], 404);
        }

        if ($status === Password::INVALID_TOKEN) {
            return response()->json(['message' => 'Token invalide ou expiré'], 400);
        }

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
