<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusPedido;
use App\Enums\TipoEntrega;
use App\Events\Pedido\EnviarPedido;
use App\Models\Complemento;
use App\Models\Pedido;
use App\Models\PedidoComplementoItem;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\TaxaEntrega;
use Carbon\Carbon;
use Exception;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PedidoController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        //Adicionado variável fora do try para poder reutilizar caso estoure erro no broadcast.
        $pedido = null;

        try {
            $data = $request->all();

            DB::beginTransaction();

            #region Pedido

            //Gera o código do pedido
            $data['codigo_pedido'] = $this->gerarCodigoPedido();

            //Seta o status do pedido
            $data['status'] = StatusPedido::EmAberto->value;

            //Valida os campos do pedido.
            $validate = $this->validator($data, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            //Valida o endereço e o tipo de entrega caso seja "Entrega"
            if ($data['tipo_entrega'] == TipoEntrega::Entrega->value) {
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
                    throw new Exception($validate->errors()->first(), 422);
                }

                //Verifica se existe ou está ativo o produto.
                $produto = Produto::with('subcategoria')->find($item['id_produto']);
                if (is_null($produto) || !$produto->ativo) {
                    $produtoDescricao = @$produto->descricao ?? "Item";
                    throw new Exception("$produtoDescricao indisponível!", 500);
                }

                //Completa os campos para salvar o item no banco de dados.
                $item['id_pedido'] = $pedido->id_pedido;
                $item['descricao'] = $produto->descricao;
                $item['descricao_subcategoria'] = $produto->subcategoria->descricao;

                $pedidoItem = PedidoItem::create($item);

                foreach ($item['complementos'] as $complementoItem) {
                    //Valida os complementos do item.
                    $validate = $this->validateComplementoItens($complementoItem);
                    if ($validate->fails()) {
                        throw new Exception($validate->errors()->first(), 422);
                    }

                    //Verifica se existe o complemento.
                    $complemento = Complemento::with('categoria')->find($complementoItem['id_complemento']);

                    if (is_null($complemento)) {
                        throw new Exception("Complemento indisponível!", 500);
                    }

                    $complementoItem['id_pedido_item'] = $pedidoItem->id_pedido_item;
                    $complementoItem['id_pedido'] = $pedido->id_pedido;
                    $complementoItem['descricao'] = $complemento->descricao;
                    $complementoItem['descricao_categoria'] = $complemento->categoria->descricao;

                    PedidoComplementoItem::create($complementoItem);
                }
            }

            #endregion

            DB::commit();

            $pedido = Pedido::with('itens')->find($pedido->id_pedido);

            Event::dispatch(new EnviarPedido($pedido));

            return $this->sendResponse($pedido);
        } catch (BroadcastException) {
            return $this->sendResponse($pedido);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with('itens')->find($id);

        if (is_null($pedido)) {
            return $this->sendResponseError("Pedido não encontrado!");
        }

        return $this->sendResponse($pedido);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->all();

        $status = $data['status'] ?? null;
        $dataInicio = $data['data_inicio'] ?? null;
        $dataFim = $data['data_fim'] ?? null;

        $query = Pedido::with('itens');

        if (!is_null($status)) {
            if ($status == StatusPedido::Aceito->value) {
                $query->whereIn('status', array(
                    StatusPedido::Aceito->value,
                    StatusPedido::EmRotaDeEntrega->value,
                    StatusPedido::ProntoParaRetirada->value
                ));
            } else {
                $query->where('status', $status);
            }
        }

        $columnDataCriado = "created_at";

        if (!is_null($dataInicio) && !is_null($dataFim)) {
            $query->whereBetween($columnDataCriado, [$dataInicio, $dataFim]);
        } else if (!is_null($dataInicio) && is_null($dataFim)) {
            $query->where($columnDataCriado, '>', $dataInicio);
        } else if (is_null($dataInicio) && !is_null($dataFim)) {
            $query->where($columnDataCriado, '<', $dataFim);
        } else {
            $query->where($columnDataCriado, '>', Carbon::now()->subDay());
        }

        $pedidos = $query->orderBy($columnDataCriado, 'desc')->get();

        $data = array(
            'count' => count($pedidos),
            'list' => $pedidos,
        );

        return $this->sendResponse($data);
    }

    public function destroy(int $id): JsonResponse
    {
        $pedido = Pedido::find($id);
        if (is_null($pedido)) {
            return $this->sendResponseError("Pedido não encontrado!");
        }

        $pedido->delete();

        return $this->sendResponse(array());
    }

    public function alterarStatusPedido(Request $request, int $idPedido): JsonResponse
    {
        try {
            $data = $request->all();

            $pedido = Pedido::find($idPedido);
            $status = StatusPedido::tryFrom($data['status']);

            if (is_null($pedido)) {
                throw new Exception("Pedido não encontrado!", 404);
            }

            if (is_null($status)) {
                throw new Exception("Status inexistente!", 404);
            }

            if ($pedido->tipo_entrega == TipoEntrega::Entrega->value) {
                if ($data['status'] == StatusPedido::ProntoParaRetirada->value) {
                    throw new Exception("Pedido com entrega não pode ser retirado!", 422);
                }
            } else {
                if ($data['status'] == StatusPedido::EmRotaDeEntrega->value) {
                    throw new Exception("Pedido para retirada não pode ser entregue!", 422);
                }
            }

            $pedido->status = $status->value;
            $pedido->save();

            return $this->sendResponse($pedido);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function cancelarPedido(int $idPedido): JsonResponse
    {
        try {
            $pedido = Pedido::find($idPedido);

            if (is_null($pedido)) {
                throw new Exception("Pedido não encontrado!", 404);
            }

            if ($pedido->status == StatusPedido::Finalizado->value) {
                throw new Exception("Pedido finalizado não pode ser cancelado!", 422);
            }

            $pedido->status = StatusPedido::Cancelado->value;
            $pedido->cancelado_at = Carbon::now();

            $pedido->save();

            return $this->sendResponse($pedido);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    ///Busca as quantidades de pedidos (Total, Em aberto e Finalizados) nas últimas 24h.
    public function countsPedido(): JsonResponse
    {
        $column = 'created_at';
        $date = Carbon::now()->subDay();

        $countTotal = Pedido::where($column, '>', $date)->count();
        $countEmAberto = Pedido::where($column, '>', $date)->where('status', StatusPedido::EmAberto->value)->count();
        $countFinalizados = Pedido::where($column, '>', $date)->where('status', StatusPedido::Finalizado->value)->count();

        return $this->sendResponse(array(
            "total_pedidos" => $countTotal,
            "em_aberto" => $countEmAberto,
            "finalizados" => $countFinalizados
        ));
    }

    private function gerarCodigoPedido(): string
    {
        return substr(uniqid(rand()), 0, 5);
    }

    private function validateItens($item): Validator
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

    private function validateComplementoItens($item): Validator
    {
        return $this->validator(
            $item,
            array(
                'id_complemento' => 'required|integer',
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
