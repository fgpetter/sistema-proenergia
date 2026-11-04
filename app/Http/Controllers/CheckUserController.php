<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class CheckUserController extends Controller
{
    public function __invoke(CheckUserRequest $request): JsonResponse|Response
    {
        $user = User::query()->where('email', $request->validated('user'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response('', 401);
        }

        return response()->json(['status' => 'ok']);
    }
}
