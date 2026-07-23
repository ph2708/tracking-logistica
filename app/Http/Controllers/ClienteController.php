<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\ProtheusSalesOrder;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function index()
    {
        return view('client.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'cnpj' => 'required|string',
        ]);

        $orderNumber = $request->order_number;

        try {
            $order = ProtheusSalesOrder::where('C5_NUM', $orderNumber)->first();
        } catch (\Exception $e) {
            Log::warning('Erro ao conectar Protheus para validação do Cliente: ' . $e->getMessage());
        }

        $tracking = Tracking::with('statusLogs')
            ->where('order_number', $orderNumber)
            ->where('type', 'entrega')
            ->first();

        if (!$tracking) {
            return back()->with('error', 'Nenhum rastreamento de entrega em andamento para o pedido informado.')->withInput();
        }

        return view('client.index', compact('tracking'));
    }
}
