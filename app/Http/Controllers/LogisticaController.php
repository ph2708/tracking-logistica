<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\StatusLog;
use App\Models\User;

class LogisticaController extends Controller
{
    public function index()
    {
        $coletas = Tracking::where('type', 'coleta')
            ->whereIn('status', ['pendente_roteirizacao', 'pendente_coleta', 'coleta_finalizada'])
            ->orderBy('created_at', 'desc')
            ->get();

        $entregas = Tracking::where('type', 'entrega')
            ->whereIn('status', ['pendente_entrega', 'enviado_cliente', 'em_transporte', 'entregue'])
            ->orderBy('created_at', 'desc')
            ->get();

        $drivers = User::where('role', 'motorista')->get();

        return view('logistica.dashboard', compact('coletas', 'entregas', 'drivers'));
    }

    public function routeOrder(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|exists:trackings,id',
            'driver_id' => 'required|exists:users,id',
            'observations_logistics' => 'nullable|string',
        ]);

        $tracking = Tracking::findOrFail($request->tracking_id);
        $tracking->update([
            'driver_id' => $request->driver_id,
            'observations_logistics' => $request->observations_logistics,
            'status' => 'pendente_coleta',
        ]);

        StatusLog::create([
            'tracking_id' => $tracking->id,
            'status' => 'pendente_coleta',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('logistica.dashboard')->with('success', 'Coleta roteirizada com sucesso. Motorista atribuído.');
    }

    public function shipOrder(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|exists:trackings,id',
            'driver_id' => 'required_if:transport_type,proprio|exists:users,id|nullable',
            'vehicle_info' => 'required_if:transport_type,proprio|string|nullable',
            'carrier_name' => 'required_if:transport_type,terceirizado|string|nullable',
        ]);

        $tracking = Tracking::findOrFail($request->tracking_id);

        if ($tracking->transport_type === 'proprio') {
            $tracking->update([
                'driver_id' => $request->driver_id,
                'vehicle_info' => $request->vehicle_info,
            ]);

            StatusLog::create([
                'tracking_id' => $tracking->id,
                'status' => 'roteirizado',
                'user_id' => auth()->id(),
            ]);
        } else {
            $tracking->update([
                'carrier_name' => $request->carrier_name,
                'status' => 'enviado_cliente',
            ]);

            StatusLog::create([
                'tracking_id' => $tracking->id,
                'status' => 'enviado_cliente',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('logistica.dashboard')->with('success', 'Entrega roteirizada com sucesso.');
    }

    public function printQRCode($id)
    {
        $tracking = Tracking::findOrFail($id);
        return view('logistica.qrcode', compact('tracking'));
    }

    public function manualClose($id)
    {
        $tracking = Tracking::findOrFail($id);

        if ($tracking->status === 'entregue' || $tracking->status === 'coleta_finalizada') {
            return back()->with('error', 'Esta operação já está concluída.');
        }

        $newStatus = ($tracking->type === 'entrega') ? 'entregue' : 'coleta_finalizada';

        $tracking->update([
            'status' => $newStatus,
            'completion_time' => now(),
            'observations_logistics' => 'Baixa Manual efetuada pela Logística (Operador: ' . auth()->user()->name . ').'
        ]);

        StatusLog::create([
            'tracking_id' => $tracking->id,
            'status' => $newStatus,
            'user_id' => auth()->id(),
            'latitude' => null,
            'longitude' => null,
        ]);

        return back()->with('success', 'Baixa manual efetuada com sucesso!');
    }
}
