<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        //Verifica se já existe uma categoria com a mesma descrição.
        $categoriaCriada = Categoria::firstWhere(DB::raw('lower(descricao)'), strtolower($data['descricao']));
        if (!is_null($categoriaCriada)) {
            return $this->sendResponseError(array('descricao' => "Já existe uma categoria com esta descrição!"), 422);
        }

        //Valida os dados vindos da requisição.
        $validate = $this->validator($data, $this->rules(), $this->messages());
        if ($validate->fails()) {
            return $this->sendResponseError($validate->errors(), 422);
        }

        $categoria = Categoria::create($data);

        return $this->sendResponse($categoria);
    }

    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::find($id);

        if (is_null($categoria)) {
            return $this->sendResponseError("Categoria não encontrada!");
        }

        return $this->sendResponse($categoria);
    }

    public function index(): JsonResponse
    {
        $categorias = Categoria::all();

        $data = array(
            'count' => count($categorias),
            'list' => $categorias,
        );

        return $this->sendResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();

        $categoria = Categoria::find($id);
        if (is_null($categoria)) {
            return $this->sendResponseError("Categoria não encontrada!");
        }

        //Atribui a descrição da categoria encontrada para depois fazer a validação.
        $categoriaDescricao = $categoria->descricao;

        $categoria->fill($data);

        //Se a descrição da requisição for direferente, valida se a mesma não está sendo utilizada por outra categoria.
        if ($categoriaDescricao != $data['descricao']) {
            $categoriaCriada = Categoria::where(DB::raw('lower(descricao)'), strtolower($data['descricao']))
                ->where('id_categoria', '!=', $id)->first();

            if (!is_null($categoriaCriada)) {
                return $this->sendResponseError(array('descricao' => "Já existe uma categoria com esta descrição!"), 422);
            }

            $validate = $this->validator($categoria->toArray(), $this->rules(), $this->messages());
            if ($validate->fails()) {
                return $this->sendResponseError($validate->errors(), 422);
            }
        }

        $categoria->save();

        return $this->sendResponse($categoria);
    }

    public function destroy(int $id): JsonResponse
    {
        $categoria = Categoria::with('subcategorias')->find($id);
        if (is_null($categoria)) {
            return $this->sendResponseError("Categoria não encontrada!");
        }

        $subcategorias = $categoria->subcategorias->toArray();

        //Valida se a categoria possui alguma subcategoria vinculada.
        if (!empty($subcategorias)) {
            return $this->sendResponseError("Não foi possível excluir.\nA categoria está sendo utilizada pela subcategoria '{$subcategorias[0]['descricao']}'!");
        }

        $categoria->delete();

        return $this->sendResponse(array());
    }

    protected function rules(): array
    {
        $table = (new Categoria())->getTable();

        return array('descricao' => "required|unique:$table|max:50");
    }

    protected function messages(): array
    {
        return array(
            'required' => "O campo descrição da categoria é obrigatório!",
            'unique' => "Já existe uma categoria com esta descrição!",
            'max' => "Descrição da categoria muito longa!",
        );
    }
}
