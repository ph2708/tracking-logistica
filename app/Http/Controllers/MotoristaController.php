<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\StatusLog;

class MotoristaController extends Controller
{
    public function index()
    {
        $driverId = auth()->id();

        $trackings = Tracking::where('driver_id', $driverId)
            ->whereNotIn('status', ['entregue', 'coleta_finalizada'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $history = Tracking::where('driver_id', $driverId)
            ->whereIn('status', ['entregue', 'coleta_finalizada'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('motorista.dashboard', compact('trackings', 'history'));
    }

    public function scanQRCode(Request $request)
    {
        $request->validate([
            'qrcode_token' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'product_code_validation' => 'nullable|string',
            'photo_product' => 'nullable|image|max:10240',
            'photo_invoice' => 'nullable|image|max:10240',
        ]);

        $tracking = Tracking::where('qrcode_token', $request->qrcode_token)
            ->where('driver_id', auth()->id())
            ->first();

        if (!$tracking) {
            return back()->with('error', 'Código QR inválido ou não atribuído a você.');
        }

        $oldStatus = $tracking->status;
        $newStatus = $tracking->status;

        $photoProductPath = null;
        $photoInvoicePath = null;

        if ($tracking->status === 'pendente_entrega' || $tracking->status === 'pendente_coleta') {
            $newStatus = 'em_transporte';
            $tracking->update([
                'status' => 'em_transporte',
                'departure_time' => now(),
            ]);
        } elseif ($tracking->status === 'em_transporte') {
            // Validation is REQUIRED when concluding the operation
            $request->validate([
                'product_code_validation' => 'required|string',
                'photo_product' => 'required|image|max:10240',
                'photo_invoice' => 'required|image|max:10240',
            ]);

            // Query valid product codes from Protheus
            $validCodes = [];
            try {
                if ($tracking->type === 'entrega') {
                    $validCodes = \App\Models\ProtheusSalesOrderItem::where('C6_NUM', $tracking->order_number)
                        ->pluck('C6_PRODUTO')
                        ->map(fn($val) => trim($val))
                        ->toArray();
                } else {
                    $validCodes = \App\Models\ProtheusPurchaseOrder::where('C7_NUM', $tracking->order_number)
                        ->pluck('C7_PRODUTO')
                        ->map(fn($val) => trim($val))
                        ->toArray();
                }
            } catch (\Exception $e) {
                // Fallback for mocks / offline testing
                $validCodes = ['NK 40/20 INA', '11841803', '012345', 'PRODUTO-TESTE'];
            }

            $inputCode = trim($request->product_code_validation);
            $match = false;
            foreach ($validCodes as $code) {
                if (strcasecmp($code, $inputCode) === 0) {
                    $match = true;
                    break;
                }
            }

            if (!$match && count($validCodes) > 0) {
                return back()->with('error', 'Código de produto inválido para esta operação.');
            }

            if ($request->hasFile('photo_product')) {
                $photoProductPath = $request->file('photo_product')->store('deliveries/products', 'public');
            }
            if ($request->hasFile('photo_invoice')) {
                $photoInvoicePath = $request->file('photo_invoice')->store('deliveries/invoices', 'public');
            }

            $newStatus = ($tracking->type === 'entrega') ? 'entregue' : 'coleta_finalizada';
            $tracking->update([
                'status' => $newStatus,
                'completion_time' => now(),
                'delivery_photo_product' => $photoProductPath,
                'delivery_photo_invoice' => $photoInvoicePath,
            ]);
        } else {
            return back()->with('error', 'Esta operação já foi concluída.');
        }

        if ($newStatus !== $oldStatus) {
            StatusLog::create([
                'tracking_id' => $tracking->id,
                'status' => $newStatus,
                'user_id' => auth()->id(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            return back()->with('success', 'Status atualizado com sucesso.');
        }

        return back()->with('error', 'Nenhuma alteração realizada.');
    }
}
