<?php

namespace App\Http\Controllers\Api;

use App\Models\TaxaEntrega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxaEntregaController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        //Valida os dados vindos da requisição.
        $validate = $this->validator($data, $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $taxaEntrega = TaxaEntrega::create($data);

        return $this->sendResponse($taxaEntrega);
    }

    public function show(int $id): JsonResponse {
        $taxaEntrega = TaxaEntrega::find($id);

        if (is_null($taxaEntrega)) {
            return $this->sendResponseError("Taxa de entrega não encontrada!");
        }

        return $this->sendResponse($taxaEntrega);
    }

    public function index(): JsonResponse {
        $taxasEntrega = TaxaEntrega::all();

        $data = array(
            'count' => count($taxasEntrega),
            'list' => $taxasEntrega,
        );

        return $this->sendResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();

        $taxaEntrega = TaxaEntrega::find($id);
        if (is_null($taxaEntrega)) {
            return $this->sendResponseError("Taxa de entrega não encontrada!");
        }

        $taxaEntrega->fill($data);

        $validate = $this->validator($taxaEntrega->toArray(), $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $taxaEntrega->save();

        return $this->sendResponse($taxaEntrega);
    }

    public function destroy(int $id): JsonResponse {
        $taxaEntrega = TaxaEntrega::find($id);
        if (is_null($taxaEntrega)) {
            return $this->sendResponseError("Taxa de entraga não encontrada!");
        }

        $taxaEntrega->delete();

        return $this->sendResponse(array());
    }

    protected function rules()
    {
        return array(
            'descricao' => 'required|max:50',
            'valor' => 'required|numeric',
        );
    }

    protected function messages()
    {
        return array(
            'descricao.required' => "O campo descrição é obrigatório!",
            'valor.required' => "O campo valor é obrigatório!",
            'descricao.max' => "Descrição muito longa!",
            'valor.numeric' => "Valor inválido!",
        );
    }
}
