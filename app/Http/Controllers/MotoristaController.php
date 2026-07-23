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
        ]);

        $tracking = Tracking::where('qrcode_token', $request->qrcode_token)
            ->where('driver_id', auth()->id())
            ->first();

        if (!$tracking) {
            return back()->with('error', 'Código QR inválido ou não atribuído a você.');
        }

        $oldStatus = $tracking->status;
        $newStatus = $tracking->status;

        if ($tracking->status === 'pendente_entrega' || $tracking->status === 'pendente_coleta') {
            $newStatus = 'em_transporte';
            $tracking->update([
                'status' => 'em_transporte',
                'departure_time' => now(),
            ]);
        } elseif ($tracking->status === 'em_transporte') {
            $newStatus = ($tracking->type === 'entrega') ? 'entregue' : 'coleta_finalizada';
            $tracking->update([
                'status' => $newStatus,
                'completion_time' => now(),
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
