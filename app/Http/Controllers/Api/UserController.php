<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->only(['name', 'email', 'password']);

        $validate = Validator::make(
            $data,
            array('name' => 'required', 'email' => 'required', 'password' => 'required'),
            array('required' => 'Campo obrigatório!')
        );

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user);
    }

    public function show(int $id): JsonResponse {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendResponseError("Usuário não encontrado!");
        }

        return response()->json($user);
    }

    public function index(): JsonResponse {
        $users = User::all();

        $data = array(
            'count' => count($users),
            'list' => $users,
        );

        return response()->json($data);
    }
}
