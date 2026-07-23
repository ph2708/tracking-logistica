<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tracking;
use App\Models\User;

class DiretoriaController extends Controller
{
    public function index()
    {
        $trackings = Tracking::with('driver', 'statusLogs.user')
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalDeliveries = Tracking::where('type', 'entrega')->count();
        $activeDeliveries = Tracking::where('type', 'entrega')->whereNotIn('status', ['entregue'])->count();
        
        $totalCollections = Tracking::where('type', 'coleta')->count();
        $activeCollections = Tracking::where('type', 'coleta')->whereNotIn('status', ['coleta_finalizada'])->count();

        $completedToday = Tracking::whereIn('status', ['entregue', 'coleta_finalizada'])
            ->whereDate('completion_time', now()->toDateString())
            ->count();

        return view('diretoria.dashboard', compact('trackings', 'totalDeliveries', 'activeDeliveries', 'totalCollections', 'activeCollections', 'completedToday'));
    }
}
