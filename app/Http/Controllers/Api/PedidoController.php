<?php

namespace App\Http\Controllers\Api;

use App\Enums\TipoEntrega;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\TaxaEntrega;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            DB::beginTransaction();

            #region Pedido

            //Gera o código do pedido
            $data['codigo_pedido'] = $this->gerarCodigoPedido();

            //Valida os campos do pedido.
            $validate = $this->validator($data, $this->rules(), $this->messages());
            if ($validate->fails()) {
                return $this->sendResponseError($validate->errors()->first(), 422);
            }

            //Valida o endereço e o tipo de entrega caso seja "Entrega"
            if ($data['tipo_entrega'] == TipoEntrega::Entrega) {
                if (is_null($data['rua']) || is_null($data['bairro']) || is_null($data['numero']) || is_null($data['cep']) || is_null($data['cidade'])) {
                    throw new Exception("É necessário informar o endereço completo para entrega!", 500);
                }

                $taxa_entrega = null;
                if (!is_null($data['id_taxa_entrega'])) {
                    $taxa_entrega = TaxaEntrega::find($data);
                }

                $data['descricao_taxa_entrega'] = is_null($taxa_entrega) ? TaxaEntrega::DESCRICAO_DEFAULT : $taxa_entrega->descricao;
                $data['valor_taxa_entrega'] = is_null($taxa_entrega) ? TaxaEntrega::VALOR_DEFAULT : $taxa_entrega->valor;
            } else {
                //Caso seja retirada, seta as propriedades de entrega para null.
                $data['rua'] = null;
                $data['bairro'] = null;
                $data['numero'] = null;
                $data['cep'] = null;
                $data['cidade'] = null;
                $data['id_taxa_entrega'] = null;
                $data['descricao_taxa_entrega'] = null;
                $data['valor_taxa_entrega'] = null;
            }

            //Validação para o troco não ser menor que o valor total.
            if (!is_null($data['troco']) && $data['troco'] < $data['valor_total']) {
                throw new Exception("Valor do troco deve ser maior que o valor total do pedido!", 500);
            }

            //Validação para o valor total não ser menor que 0.
            if ($data['valor_total'] <= 0) {
                throw new Exception("Valor total do pedido deve ser maior que 0!", 500);
            }

            $pedido = Pedido::create($data);

            #endregion

            #region Itens

            //Verifica se existe itens no pedido.
            if (@empty($data['itens'])) {
                throw new Exception("Selecione pelo menos um item para criar o pedido.", 500);
            }

            //Percorre todos os itens do pedido.
            foreach ($data['itens'] as $item) {
                //Valida os itens do pedido.
                $validate = $this->validateItens($item);
                if ($validate->fails()) {
                    return $this->sendResponseError($validate->errors()->first(), 422);
                }

                //Verifica se existe ou está ativo o produto.
                $produto = Produto::with('subcategoria')->find($item['id_produto']);
                if (is_null($produto) || !$produto->ativo) {
                    $produtoDescricao = @$produto->descricao ?? "Item";
                    throw new Exception("{$produtoDescricao} indisponível!", 500);
                }

                //Completa os campos para salvar o item no banco de dados.
                $item['id_pedido'] = $pedido->id_pedido;
                $item['descricao'] = $produto->descricao;
                $item['descricao_subcategoria'] = $produto->subcategoria->descricao;

                PedidoItem::create($item);
            }

            #endregion

            DB::commit();

            return $this->sendResponse($pedido);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function show(int $id): JsonResponse {
        $pedido = Pedido::with('itens')->find($id);

        if (is_null($pedido)) {
            return $this->sendResponseError("Pedido não encontrado!");
        }

        return $this->sendResponse($pedido);
    }

    public function index(): JsonResponse {
        $pedidos = Pedido::with('itens')->get();

        $data = array(
            'count' => count($pedidos),
            'list' => $pedidos,
        );

        return $this->sendResponse($data);
    }

    public function destroy(int $id): JsonResponse {
        $pedido = Pedido::find($id);
        if (is_null($pedido)) {
            return $this->sendResponseError("Pedido não encontrado!");
        }

        $pedido->delete();

        return $this->sendResponse(array());
    }

    private function gerarCodigoPedido(): string
    {
        return substr(uniqid(rand()), 0, 5);
    }

    private function validateItens($item)
    {
        return $this->validator(
            $item,
            array(
                'id_produto' => 'required|integer',
                'valor_unitario' => 'required|numeric',
                'quantidade' => 'required|numeric',
            ),
            array(
                'required' => "Campo obrigatório!",
                'numeric' => "Valor inválido!",
                'integer' => "Valor inválido!",
            ),
        );
    }

    protected function rules(): array
    {
        return array(
            'codigo_pedido' => 'required',
            'nome' => 'required|max:50',
            'telefone' => 'required|max:11',
            'rua' => 'nullable|max:80',
            'bairro' => 'nullable|max:50',
            'cep' => 'nullable|max:8',
            'cidade' => 'nullable|max:50',
            'valor_total' => 'required|numeric',
            'troco' => 'nullable|numeric',
            'valor_pago' => 'nullable|numeric',
            'forma_pagamento' => 'nullable|integer',
            'tipo_entrega' => 'required|integer',
            'observacao' => 'nullable|max:250',
            'tempo_estimado' => 'required|max:50',
            'id_taxa_entrega' => 'nullable|integer',
            'descricao_taxa_entrega' => 'nullable|max:50',
            'valor_taxa_entrega' => 'nullable|numeric',
        );
    }

    protected function messages(): array
    {
        return array(
            'required' => "Campo obrigatório!",
            'nome.max' => "Nome muito extenso!",
            'telefone.max' => "Telefone inválido!",
            'max' => "Descrição muito longa!",
            'cep.max' => "Cep inválido!",
            'valor_total.numeric' => "Valor total inválido!",
            'troco.numeric' => "Valor do troco inválido!",
            'valor_pago.numeric' => "Valor pago inválido!",
            'forma_pagamento.integer' => "Forma de pagamento inválida!",
            'tipo_entrega.integer' => "Tipo de entrega inválida!",
            'id_taxa_entrega.integer' => "Taxa de entrega inválida!",
            'valor_taxa_entrega.numeric' => "Valor da taxa de entrega inválido!",
        );
    }
}
