<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubcategoriaController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        //Verifica se a categoria foi passada na requisição.
        if (!isset($data['id_categoria'])) {
            return $this->sendResponseError(array('id_categoria' => "É necessário informar uma categoria!"), 422);
        }

        //Verifica se a descrição foi passada na requisição.
        if (!isset($data['descricao'])) {
            return $this->sendResponseError(array('descricao' => "O campo descrição da subcategoria é obrigatório!"), 422);
        }

        //Verifica se já existe a categoria
        $categoria = Categoria::find($data['id_categoria']);
        if (is_null($categoria)) {
            return $this->sendResponseError(array('id_categoria' => "Categoria não encontrada!"), 422);
        }

        //Valida se já existe uma subcategoria com a mesma descrição e categoria
        $subcategoriaCriada = Subcategoria::where(DB::raw('lower(descricao)'), strtolower($data['descricao']))
            ->where('id_categoria', $data['id_categoria'])->first();

        if (!is_null($subcategoriaCriada)) {
            return $this->sendResponseError(array('descricao' => "Já existe uma subcategoria com esta descrição!"), 422);
        }

        //Valida os campos da subcategoria
        $validate = $this->validator($data, $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $subcategoria = Subcategoria::create($data);

        return $this->sendResponse($subcategoria);
    }

    public function show(int $id): JsonResponse
    {
        $subcategoria = Subcategoria::find($id);

        if (is_null($subcategoria)) {
            return $this->sendResponseError("Subcategoria não encontrada!");
        }

        return $this->sendResponse($subcategoria);
    }

    public function index(): JsonResponse
    {
        $subcategorias = Subcategoria::all();

        $data = array(
            'count' => count($subcategorias),
            'list' => $subcategorias,
        );

        return $this->sendResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();

        //Verifica se a descrição foi passada na requisição.
        if (!isset($data['descricao'])) {
            return $this->sendResponseError(array('descricao' => "O campo descrição da subcategoria é obrigatório!"), 422);
        }

        $subcategoria = Subcategoria::find($id);
        if (is_null($subcategoria)) {
            return $this->sendResponseError("Subcategoria não encontrada!");
        }

        $subcategoria->fill($data);

        //Verifica se existe uma subcategoria com esta descrição sendo utilizada pelo mesmo id_categoria.
        $subcategoriaCriada = Subcategoria::where(DB::raw('lower(descricao)'), strtolower($data['descricao']))
            ->where('id_categoria', $subcategoria->id_categoria)
            ->where('id_subcategoria', '!=', $id)->first();

        if (!is_null($subcategoriaCriada)) {
            return $this->sendResponseError(array('descricao' => "Já existe uma subcategoria com esta descrição!"), 422);
        }

        $validate = $this->validator($subcategoria->toArray(), $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $subcategoria->save();

        return $this->sendResponse($subcategoria);
    }

    public function destroy(int $id): JsonResponse
    {
        $subcategoria = Subcategoria::with('produtos')->find($id);
        if (is_null($subcategoria)) {
            return $this->sendResponseError("Subcategoria não encontrada!");
        }

        $produtos = $subcategoria->produtos->toArray();

        //Valida se a subcategoria possui algum produto vinculado.
        if (!empty($produtos)) {
            return $this->sendResponseError("Não foi possível excluir.\nA subcategoria está sendo utilizada pelo produtps '{$produtos[0]['descricao']}'!");
        }

        $subcategoria->delete();

        return $this->sendResponse(array());
    }

    protected function rules(): array
    {
        return array(
            'descricao' => 'required|max:50',
            'id_categoria' => 'required'
        );
    }

    protected function messages(): array
    {
        return array(
            'descricao.required' => "O campo descrição da subcategoria é obrigatório!",
            'id_categoria.required' => "É necessário informar uma categoria!",
            'max' => "Descrição da subcategoria muito longa!",
        );
    }
}
