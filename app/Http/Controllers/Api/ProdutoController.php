<?php

namespace App\Http\Controllers\Api;

use App\Models\Produto;
use App\Models\Subcategoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutoController extends BaseController
{
    public function store(Request $request):JsonResponse
    {
        $data = $request->all();

        //Verifica se a subcategoria foi passada na requisição.
        if (!isset($data['id_subcategoria'])) {
            return $this->sendResponseError(array('id_subcategoria' => 'É necessário informar uma subcategoria!'));
        }

        //Verifica se já existe a subcategoria
        $subcategoria = Subcategoria::find($data['id_subcategoria']);
        if (is_null($subcategoria)) {
            return $this->sendResponseError(array('id_subcategoria' => "Subcategoria não encontrada!"), 422);
        }

        //Valida os campos do produto
        $validate = $this->validator($data, $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $produto = Produto::create($data);

        return $this->sendResponse($produto);
    }

    public function show(int $id): JsonResponse {
        $produto = Produto::find($id);

        if (is_null($produto)) {
            return $this->sendResponseError("Produto não encontrado!");
        }

        return $this->sendResponse($produto);
    }

    public function index(): JsonResponse {
        $produtos = Produto::all();

        $data = array(
            'count' => count($produtos),
            'list' => $produtos,
        );

        return $this->sendResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse {
        $data = $request->all();

        //Verifica se existe esse produto cadastrado.
        $produto = Produto::find($id);
        if (is_null($produto)) {
            $this->sendResponseError("Produto não encontrado!");
        }

        //Verifica se existe a subcategoria passada na requisição.
        if (isset($data['id_subcategoria'])) {
            $subcategoria = Subcategoria::find($data['id_subcategoria']);

            if (is_null($subcategoria)) {
                return $this->sendResponseError(array('id_subcategoria' => "Subcategoria não encontrada!"));
            }
        }

        $produto->fill($data);

        $validate = $this->validator($produto->toArray(), $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $produto->save();

        return $this->sendResponse($produto);
    }

    public function destroy(int $id): JsonResponse {
        $produto = Produto::find($id);
        if (is_null($produto)) {
            return $this->sendResponseError("Produto não encontrado!");
        }

        $produto->delete();

        return $this->sendResponse(array());
    }

    protected function rules()
    {
        return array(
            'descricao' => 'required|max:50',
            'id_subcategoria' => 'required',
            'sobre' => 'max:250',
            'preco' => 'required|numeric',
            'ativo' => 'nullable|boolean',
        );
    }

    protected function messages()
    {
        return array(
            'descricao.required' => "O campo descrição do produto é obrigatório!",
            'id_subcategoria.required' => "É necessário informar uma subcategoria!",
            'preco.required' => "É necessário informar o preço do produto!",
            'sobre.max' => "O texto sobre o produto muito longa!",
            'numeric' => "Valor inválido!",
            'boolean' => "Valor inválido!",
        );
    }
}
