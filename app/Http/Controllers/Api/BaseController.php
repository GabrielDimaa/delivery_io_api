<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

abstract class BaseController extends Controller
{
    protected function sendResponse($data): JsonResponse
    {
        $response = array(
            'success' => true,
            'data' => $data,
        );

        return response()->json($response);
    }

    protected function sendResponseError($error, $code = 404): JsonResponse
    {
        $response = array(
            'success' => false,
            'error' => $error,
        );

        if ($code == 0) $code = 500;

        return response()->json($response, $code);
    }

    protected function validator(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return Validator::make($data, $rules, $messages, $customAttributes);
    }

    abstract protected function rules();
    abstract protected function messages();
}
