<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\StatusLog;
use App\Models\ProtheusSalesOrder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EstoqueController extends Controller
{
    public function index()
    {
        $trackings = Tracking::where('type', 'entrega')->orderBy('created_at', 'desc')->get();
        return view('estoque.dashboard', compact('trackings'));
    }

    public function searchOrder(Request $request)
    {
        $orderNumber = $request->query('order_number');
        if (!$orderNumber) {
            return response()->json(['error' => 'Número do pedido é obrigatório.'], 400);
        }

        try {
            $order = ProtheusSalesOrder::with('items')->where('C5_NUM', $orderNumber)->first();

            if (!$order) {
                return $this->getMockOrder($orderNumber);
            }

            return response()->json([
                'success' => true,
                'is_mock' => false,
                'order_number' => trim($order->C5_NUM),
                'client_code' => trim($order->C5_CLIENTE),
                'client_store' => trim($order->C5_LOJACLI),
                'emission_date' => $order->C5_EMISSAO,
                'items' => $order->items->map(function($item) {
                    return [
                        'item' => trim($item->C6_ITEM),
                        'product' => trim($item->C6_PRODUTO),
                        'description' => trim($item->C6_DESCRI),
                        'quantity' => floatval($item->C6_QTDVEN),
                        'value' => floatval($item->C6_VALOR),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::warning('Erro ao conectar com Protheus (usando fallback mock): ' . $e->getMessage());
            return $this->getMockOrder($orderNumber);
        }
    }

    public function storeTracking(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'transport_type' => 'required|string|in:proprio,terceirizado',
            'observations_origin' => 'nullable|string',
            'delivery_cep' => 'required|string',
            'delivery_street' => 'required|string|max:255',
            'delivery_number' => 'required|string|max:50',
            'delivery_neighborhood' => 'required|string|max:100',
            'delivery_city' => 'required|string|max:100',
            'delivery_state' => 'required|string|max:2',
            'departure_time' => 'required_if:transport_type,proprio|nullable|date',
            'dimensions' => 'required_if:transport_type,terceirizado|nullable|string',
            'weight' => 'required_if:transport_type,terceirizado|nullable|numeric',
            'value' => 'required_if:transport_type,terceirizado|nullable|numeric',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $existing = Tracking::where('order_number', $request->order_number)->where('type', 'entrega')->first();
        if ($existing) {
            return redirect()->route('estoque.dashboard')->with('error', 'Já existe um rastreamento iniciado para este Pedido de Venda.');
        }

        $invoicePath = null;
        if ($request->hasFile('invoice_file')) {
            $invoicePath = $request->file('invoice_file')->store('invoices', 'public');
        }

        // Format delivery address
        $deliveryAddress = sprintf(
            "%s, Nº %s - %s, %s - %s (CEP: %s)",
            $request->delivery_street,
            $request->delivery_number,
            $request->delivery_neighborhood,
            $request->delivery_city,
            strtoupper($request->delivery_state),
            $request->delivery_cep
        );

        $qrcodeToken = 'PV-' . $request->order_number . '-' . Str::upper(Str::random(6));

        $tracking = Tracking::create([
            'type' => 'entrega',
            'order_number' => $request->order_number,
            'status' => 'pendente_entrega',
            'observations_origin' => $request->observations_origin,
            'transport_type' => $request->transport_type,
            'departure_time' => $request->departure_time,
            'dimensions' => $request->dimensions,
            'weight' => $request->weight,
            'value' => $request->value,
            'invoice_path' => $invoicePath,
            'collection_address' => $deliveryAddress,
            'qrcode_token' => $qrcodeToken,
        ]);

        StatusLog::create([
            'tracking_id' => $tracking->id,
            'status' => 'pendente_entrega',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('estoque.dashboard')->with('success', 'Rastreamento de entrega iniciado com sucesso. QR Code gerado.');
    }

    protected function getMockOrder($orderNumber)
    {
        return response()->json([
            'success' => true,
            'is_mock' => true,
            'order_number' => $orderNumber,
            'client_code' => 'CLI9999',
            'client_store' => '01',
            'emission_date' => date('Ymd'),
            'items' => [
                [
                    'item' => '01',
                    'product' => 'PROD-MOCK-001',
                    'description' => 'PRODUTO SIMULADO DO PROTHEUS',
                    'quantity' => 10,
                    'value' => 1500.00
                ],
                [
                    'item' => '02',
                    'product' => 'PROD-MOCK-002',
                    'description' => 'OUTRO ITEM SIMULADO DE TESTE',
                    'quantity' => 5,
                    'value' => 750.50
                ]
            ]
        ]);
    }
}
