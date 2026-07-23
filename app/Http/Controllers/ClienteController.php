<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\ProtheusSalesOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        // Clean CNPJ (only numbers)
        $cnpjDigits = preg_replace('/\D/', '', $request->cnpj);

        try {
            $order = ProtheusSalesOrder::where('C5_NUM', $orderNumber)->first();
            
            if ($order) {
                // Query customer table SA1010 in Protheus to check CNPJ (A1_CGC)
                $customer = DB::connection('protheus')
                    ->table('SA1010')
                    ->where('A1_COD', $order->C5_CLIENTE)
                    ->where('A1_LOJA', $order->C5_LOJACLI)
                    ->where('D_E_L_E_T_', ' ')
                    ->first();

                if ($customer) {
                    $dbCnpj = preg_replace('/\D/', '', $customer->A1_CGC);
                    if ($dbCnpj !== $cnpjDigits) {
                        return back()->with('error', 'O CNPJ informado não coincide com o cliente do pedido.')->withInput();
                    }
                }
            }
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
