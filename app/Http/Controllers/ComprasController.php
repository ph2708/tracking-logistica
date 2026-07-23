<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\StatusLog;
use App\Models\ProtheusPurchaseOrder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ComprasController extends Controller
{
    public function index()
    {
        $trackings = Tracking::where('type', 'coleta')->orderBy('created_at', 'desc')->get();
        return view('compras.dashboard', compact('trackings'));
    }

    public function searchOrder(Request $request)
    {
        $orderNumber = $request->query('order_number');
        if (!$orderNumber) {
            return response()->json(['error' => 'Número do pedido é obrigatório.'], 400);
        }

        try {
            // SC7010 is the Purchase Order items table in Protheus. Group items by C7_NUM.
            $items = ProtheusPurchaseOrder::where('C7_NUM', $orderNumber)->get();

            if ($items->isEmpty()) {
                return $this->getMockOrder($orderNumber);
            }

            $firstItem = $items->first();

            return response()->json([
                'success' => true,
                'is_mock' => false,
                'order_number' => trim($firstItem->C7_NUM),
                'supplier_code' => trim($firstItem->C7_FORNECE),
                'supplier_store' => trim($firstItem->C7_LOJA),
                'emission_date' => $firstItem->C7_EMISSAO,
                'items' => $items->map(function($item) {
                    return [
                        'item' => trim($item->C7_ITEM),
                        'product' => trim($item->C7_PRODUTO),
                        'description' => trim($item->C7_DESCRI),
                        'quantity' => floatval($item->C7_QUANT),
                        'value' => floatval($item->C7_TOTAL),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::warning('Erro ao conectar com Protheus (usando fallback mock compras): ' . $e->getMessage());
            return $this->getMockOrder($orderNumber);
        }
    }

    public function storeTracking(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'observations_origin' => 'nullable|string',
            'collection_cep' => 'required|string',
            'collection_street' => 'required|string|max:255',
            'collection_number' => 'required|string|max:50',
            'collection_neighborhood' => 'required|string|max:100',
            'collection_city' => 'required|string|max:100',
            'collection_state' => 'required|string|max:2',
            'collection_schedule' => 'required|date',
        ]);

        $existing = Tracking::where('order_number', $request->order_number)->where('type', 'coleta')->first();
        if ($existing) {
            return redirect()->route('compras.dashboard')->with('error', 'Já existe um rastreamento iniciado para este Pedido de Compra.');
        }

        // Construct full address string
        $collectionAddress = sprintf(
            "%s, Nº %s - %s, %s - %s (CEP: %s)",
            $request->collection_street,
            $request->collection_number,
            $request->collection_neighborhood,
            $request->collection_city,
            strtoupper($request->collection_state),
            $request->collection_cep
        );

        $qrcodeToken = 'PC-' . $request->order_number . '-' . Str::upper(Str::random(6));

        $tracking = Tracking::create([
            'type' => 'coleta',
            'order_number' => $request->order_number,
            'status' => 'pendente_roteirizacao',
            'observations_origin' => $request->observations_origin,
            'collection_address' => $collectionAddress,
            'collection_schedule' => $request->collection_schedule,
            'qrcode_token' => $qrcodeToken,
        ]);

        StatusLog::create([
            'tracking_id' => $tracking->id,
            'status' => 'pendente_roteirizacao',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('compras.dashboard')->with('success', 'Pedido de coleta enviado para a logística com sucesso. QR Code gerado.');
    }

    protected function getMockOrder($orderNumber)
    {
        return response()->json([
            'success' => true,
            'is_mock' => true,
            'order_number' => $orderNumber,
            'supplier_code' => 'FORN1234',
            'supplier_store' => '02',
            'emission_date' => date('Ymd'),
            'items' => [
                [
                    'item' => '01',
                    'product' => 'MAT-IND-001',
                    'description' => 'MATERIA PRIMA SIMULADA COMPRA',
                    'quantity' => 100,
                    'value' => 25000.00
                ],
                [
                    'item' => '02',
                    'product' => 'MAT-IND-002',
                    'description' => 'INSUMO SIMULADO COMPRA',
                    'quantity' => 50,
                    'value' => 12500.00
                ]
            ]
        ]);
    }
}
