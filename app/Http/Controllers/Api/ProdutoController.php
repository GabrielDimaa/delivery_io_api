<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use App\Models\Produto;
use App\Models\Subcategoria;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutoController extends BaseController
{
    public function store(Request $request):JsonResponse
    {
        try {
            $data = $request->all();

            //Verifica se a categoria foi passada na requisição.
            if (!isset($data['id_categoria'])) {
                throw new Exception("É necessário informar uma categoria!", 422);
            }

            //Verifica se já existe a categoria.
            $categoria = Categoria::find($data['id_categoria']);
            if (is_null($categoria)) {
                throw new Exception("Categoria não encontrada!");
            }

            //Verifica se a subcategoria foi passada na requisição.
            if (!isset($data['id_subcategoria'])) {
                throw new Exception("É necessário informar uma subcategoria!", 422);
            }

            //Verifica se já existe a subcategoria.
            $subcategoria = Subcategoria::find($data['id_subcategoria']);
            if (is_null($subcategoria)) {
                throw new Exception("Subcategoria não encontrada!");
            }

            //Verifica se a subcategoria pertence a subcategoria passada na requisição.
            if ($subcategoria->id_categoria != $data['id_categoria']) {
                throw new Exception("Subcategoria não pertence a categoria selecionada.", 422);
            }

            //Valida os campos do produto
            $validate = $this->validator($data, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            $produto = Produto::create($data);

            return $this->sendResponse($produto);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function show(int $id): JsonResponse {
        $produto = Produto::with("categoria", "subcategoria")->find($id);

        if (is_null($produto)) {
            return $this->sendResponseError("Produto não encontrado!");
        }

        return $this->sendResponse($produto);
    }

    public function index(): JsonResponse {
        $produtos = Produto::with("categoria", "subcategoria")->get();

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
                return $this->sendResponseError("Subcategoria não encontrada!");
            }
        }

        $produto->fill($data);

        $validate = $this->validator($produto->toArray(), $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors()->first(), 422);
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
            'sobre.max' => "O texto sobre o produto está muito longa!",
            'numeric' => "Preço inválido!",
            'ativo.boolean' => "Valor do campo ativo é inválido!",
        );
    }
}
