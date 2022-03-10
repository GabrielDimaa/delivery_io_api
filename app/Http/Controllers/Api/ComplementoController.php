<?php

namespace App\Http\Controllers\Api;

use App\Models\Categoria;
use App\Models\Complemento;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ComplementoController extends BaseController
{
    public function store(Request $request): JsonResponse
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

            if (isset($data['descricao'])) {
                //Verifica se existe um complemento com esta descrição sendo utilizada pelo mesmo id_categoria.
                $complementoCriado = Complemento::where(DB::raw('lower(descricao)'), strtolower($data['descricao']))
                    ->where('id_categoria', $data['id_categoria'])->first();

                if (!is_null($complementoCriado)) {
                    throw new Exception("Já existe um complemento com esta descrição!", 422);
                }
            }

            //Valida os dados vindos da requisição.
            $validate = $this->validator($data, $this->rules(), $this->messages());
            if ($validate->fails()) {
                return $this->sendResponseError($validate->errors()->first(), 422);
            }

            $complemento = Complemento::create($data);
            $complemento = Complemento::with("categoria")->find($complemento->id_complemento);

            return $this->sendResponse($complemento);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function show(int $id): JsonResponse {
        $complemento = Complemento::with('categoria')->find($id);

        if (is_null($complemento)) {
            return $this->sendResponseError("Complemento não encontrado!");
        }

        return $this->sendResponse($complemento);
    }

    public function index(): JsonResponse {
        $complemento = Complemento::all();

        $data = array(
            'count' => count($complemento),
            'list' => $complemento,
        );

        return $this->sendResponse($data);
    }

    public function getCategoriasComplementos(): JsonResponse {
        $complemento = Categoria::with('complementos')->get();

        $data = array(
            'count' => count($complemento),
            'list' => $complemento,
        );

        return $this->sendResponse($data);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->all();

            $complemento = Complemento::find($id);
            if (is_null($complemento)) {
                return $this->sendResponseError("Complemento não encontrado!");
            }

            if (isset($data['id_categoria'])) {
                //Verifica se já existe a categoria.
                $categoria = Categoria::find($data['id_categoria']);

                if (is_null($categoria)) {
                    throw new Exception("Categoria não encontrada!");
                }
            }

            $complemento->fill($data);

            //Verifica se existe um complemento com esta descrição sendo utilizada pelo mesmo id_categoria.
            $complementoCriado = Complemento::where(DB::raw('lower(descricao)'), strtolower($data['descricao']))
                ->where('id_categoria', $complemento->id_categoria)
                ->where('id_complemento', '!=', $complemento->id_complemento)->first();

            if (!is_null($complementoCriado)) {
                throw new Exception("Já existe um complemento com esta descrição!", 422);
            }

            $validate = $this->validator($complemento->toArray(), $this->rules(), $this->messages());
            if ($validate->fails()) {
                return $this->sendResponseError($validate->errors()->first(), 422);
            }

            $complemento->save();

            return $this->sendResponse($complemento);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function destroy(int $id): JsonResponse {
        $complemento = Complemento::find($id);
        if (is_null($complemento)) {
            return $this->sendResponseError("Complemento não encontrado!");
        }

        $complemento->delete();

        return $this->sendResponse(array());
    }

    protected function rules(): array
    {
        return array(
            'descricao' => 'required|max:50',
            'preco' => 'required|numeric',
            'id_categoria' => 'required',
        );
    }

    protected function messages(): array
    {
        return array(
            'descricao.required' => "O campo descrição é obrigatório!",
            'preco.required' => "O campo preço é obrigatório!",
            'numeric' => "Preço inválido!",
            'id_categoria.required' => "É obrigatório informar uma categoria!",
        );
    }
}
