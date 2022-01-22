<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CategoriaController extends BaseController
{
    const MESSAGE_SUBCATEGORIAS = "A categoria precisa de pelo menos uma subcategoria!";

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            DB::beginTransaction();

            //Verifica se a descrição foi passada na requisição.
            if (!isset($data['descricao'])) {
                throw new Exception("O campo descrição da categoria é obrigatório!", 422);
            }

            //Verifica se já existe uma categoria com a mesma descrição.
            $categoriaCriada = Categoria::firstWhere(DB::raw('lower(descricao)'), strtolower($data['descricao']));
            if (!is_null($categoriaCriada)) {
                throw new Exception("Já existe uma categoria com esta descrição!", 422);
            }

            //Valida os dados vindos da requisição.
            $validate = $this->validator($data, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            //Salva a categoria
            $categoria = Categoria::create($data);

            //Percorre a lista de subcategorias vindo da requisição para salvá-las
            foreach ($data['subcategorias'] as $subcategoria) {
                $subcategoria['id_categoria'] = $categoria->id_categoria;

                Subcategoria::create($subcategoria);
            }

            DB::commit();

            $categoria = Categoria::with('subcategorias')->find($categoria->id_categoria);

            return $this->sendResponse($categoria);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::with('subcategorias')->find($id);

        if (is_null($categoria)) {
            return $this->sendResponseError("Categoria não encontrada!");
        }

        return $this->sendResponse($categoria);
    }

    public function index(): JsonResponse
    {
        $categorias = Categoria::with('subcategorias')->get();

        $data = array(
            'count' => count($categorias),
            'list' => $categorias,
        );

        return $this->sendResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->all();

            DB::beginTransaction();

            $categoria = Categoria::find($id);
            if (is_null($categoria)) {
                throw new Exception("Categoria não encontrada!");
            }

            //Verifica se a descrição foi passada na requisição.
            if (!isset($data['descricao'])) {
                throw new Exception("O campo descrição da categoria é obrigatório!", 422);
            }

            //Verifica se existe alguma subcategoria passada na requisição.
            if (!isset($data['subcategorias'])) {
                throw new Exception(self::MESSAGE_SUBCATEGORIAS, 422);
            }

            $subcategoriasDeletadas = array();
            $subcategoriasNaoDeletadas = array();

            foreach ($data['subcategorias'] as $subcategoria) {
                if ($subcategoria['deleted']) {
                    $subcategoriasDeletadas[] = $subcategoria;
                } else {
                    $subcategoriasNaoDeletadas[] = $subcategoria;
                }
            }

            //Verifica se existe alguma subcategoria que não esteja deletada.
            if (empty($subcategoriasNaoDeletadas)) {
                throw new Exception(self::MESSAGE_SUBCATEGORIAS, 422);
            }

            //Deleta todas as subcategorias que foram passadas na requisição.
            foreach ($subcategoriasDeletadas as $sub) {
                $subcategoria = Subcategoria::with('produtos')->find($sub['id_subcategoria']);

                //Valida se a subcategoria não está sendo utilizada por nenhum produto.
                if (!is_null($subcategoria)) {
                    $produtos = $subcategoria->produtos->toArray();
                    if (!empty($produtos)) {
                        throw new Exception("A subcategoria '$subcategoria->descricao' está sendo utilizada pelo produto '{$produtos[0]['descricao']}'!", 422);
                    }

                    $subcategoria->delete();
                }
            }

            //Atualiza todas as subcategorias passadas na requisição
            foreach ($subcategoriasNaoDeletadas as $sub) {
                if (!is_null($sub['id_subcategoria'])) {
                    $subcategoria = Subcategoria::find($sub['id_subcategoria']);
                    $subcategoria->fill($sub);

                    //Verifica se existe uma subcategoria com esta descrição sendo utilizada pelo mesmo id_categoria.
                    $subcategoriaCriada = Subcategoria::where(DB::raw('lower(descricao)'), strtolower($sub['descricao']))
                        ->where('id_categoria', $subcategoria->id_categoria)
                        ->where('id_subcategoria', '!=', $subcategoria->id_subcategoria)->first();

                    if (!is_null($subcategoriaCriada)) {
                        throw new Exception("Já existe uma subcategoria com esta descrição!", 422);
                    }

                    $validate = $this->validator($subcategoria->toArray(), $this->rulesSubcategoria(), $this->messagesSubcategoria());
                    if ($validate->fails()) {
                        throw new Exception($validate->errors()->first(), 422);
                    }

                    $subcategoria->save();
                } else {
                    //Verifica se existe uma subcategoria com esta descrição sendo utilizada pelo mesmo id_categoria.
                    $subcategoriaCriada = Subcategoria::where(DB::raw('lower(descricao)'), strtolower($sub['descricao']))
                        ->where('id_categoria', $sub['id_categoria'])->first();

                    if (!is_null($subcategoriaCriada)) {
                        throw new Exception("Já existe uma subcategoria com esta descrição!", 422);
                    }

                    $validate = $this->validator($sub, $this->rulesSubcategoria(), $this->messagesSubcategoria());
                    if ($validate->fails()) {
                        throw new Exception($validate->errors()->first(), 422);
                    }

                    Subcategoria::create($sub);
                }
            }

            //Atribui a descrição da categoria encontrada para depois fazer a validação.
            $categoriaDescricao = $categoria->descricao;

            //Atualiza os novos valores para a categoria que será salva.
            $categoria->fill($data);

            //Se a descrição da requisição for diferente, valida se a mesma não está sendo utilizada por outra categoria.
            if ($categoriaDescricao != $data['descricao']) {
                $categoriaCriada = Categoria::where(DB::raw('lower(descricao)'), strtolower($data['descricao']))
                    ->where('id_categoria', '!=', $id)->first();

                if (!is_null($categoriaCriada)) {
                    throw new Exception("Já existe uma categoria com esta descrição!", 422);
                }

                $validate = $this->validator($categoria->toArray(), $this->rules(), $this->messages());
                if ($validate->fails()) {
                    throw new Exception($validate->errors()->first(), 422);
                }
            }

            $categoria->save();

            DB::commit();

            $categoria = Categoria::with('subcategorias')->find($id);

            return $this->sendResponse($categoria);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $categoria = Categoria::with('subcategorias')->find($id);
            if (is_null($categoria)) {
                throw new Exception("Categoria não encontrada!");
            }

            foreach ($categoria->subcategorias as $sub) {
                $subcategoria = Subcategoria::with('produtos')->find($sub->id_subcategoria);

                $produtos = $subcategoria->produtos->toArray();
                if (!empty($produtos)) {
                    throw new Exception("A subcategoria '$subcategoria->descricao' está sendo utilizada pelo produto '{$produtos[0]['descricao']}'!", 422);
                }
            }

            $categoria->delete();

            DB::commit();

            return $this->sendResponse(array());
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    protected function rules(): array
    {
        $table = (new Categoria())->getTable();
        return array(
            'descricao' => "required|unique:$table|max:50",
            'subcategorias' => "required|array|min:1",
        );
    }

    protected function messages(): array
    {
        return array(
            'descricao.required' => "O campo descrição da categoria é obrigatório!",
            'unique' => "Já existe uma categoria com esta descrição!",
            'max' => "Descrição da categoria muito longa!",
            'subcategorias.required' => self::MESSAGE_SUBCATEGORIAS,
            'array' => self::MESSAGE_SUBCATEGORIAS,
            'min' => self::MESSAGE_SUBCATEGORIAS,
        );
    }

    protected function rulesSubcategoria(): array
    {
        return array(
            'descricao' => 'required|max:50',
            'id_categoria' => 'required'
        );
    }

    protected function messagesSubcategoria(): array
    {
        return array(
            'descricao.required' => "O campo descrição da subcategoria é obrigatório!",
            'id_categoria.required' => "É necessário informar uma categoria!",
            'max' => "Descrição da subcategoria muito longa!",
        );
    }
}
