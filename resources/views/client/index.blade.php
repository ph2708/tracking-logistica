@extends('layouts.app')

@section('title', 'Rastrear Pedido - Portal do Cliente')

@section('styles')
<style>
    .client-container {
        max-width: 650px;
        margin: 2rem auto;
    }
    .client-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .client-logo {
        font-size: 3rem;
        color: var(--accent-blue);
        margin-bottom: 0.5rem;
    }
    .client-title {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.025em;
    }
    .client-subtitle {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    /* Client timeline styling */
    .timeline-tracker {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }
    .step-list {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 2.5rem;
    }
    .step-list::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--border-color);
        z-index: 1;
    }
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 33.33%;
        position: relative;
        z-index: 2;
    }
    .step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #1e293b;
        border: 3px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    .step-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-align: center;
    }

    /* Active & Completed step states */
    .step-item.completed .step-circle {
        background: var(--accent-blue);
        border-color: var(--accent-blue);
        color: #0f172a;
        box-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
    }
    .step-item.completed .step-label {
        color: var(--accent-blue);
    }
    .step-item.active .step-circle {
        background: #1e293b;
        border-color: var(--accent-green);
        color: var(--accent-green);
        box-shadow: 0 0 12px rgba(52, 211, 153, 0.3);
    }
    .step-item.active .step-label {
        color: var(--accent-green);
    }

    /* Connection line styling for completed steps */
    .step-progress-bar {
        position: absolute;
        top: 20px;
        left: 0;
        height: 4px;
        background: var(--accent-blue);
        z-index: 1;
        transition: width 0.5s ease;
    }
    
    .tracking-details-box {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="client-container">
    <div class="client-header">
        <div class="client-logo" style="margin-bottom: 1.5rem;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-height: 70px; object-fit: contain;">
        </div>
        <h1 class="client-title">Rastreamento de Pedido</h1>
        <p class="client-subtitle">Insira as informações do seu pedido para acompanhar o status</p>
    </div>

    <!-- Search Form -->
    <div class="glass-card" style="margin-bottom: 2rem;">
        <form action="{{ route('client.search') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="order_number" class="form-label">Número do Pedido de Venda</label>
                <input type="text" name="order_number" id="order_number" class="form-control" placeholder="Digite o número do pedido" value="{{ old('order_number') }}" required>
            </div>

            <div class="form-group">
                <label for="cnpj" class="form-label">CNPJ do Cliente</label>
                <input type="text" name="cnpj" id="cnpj" class="form-control" placeholder="00.000.000/0000-00" value="{{ old('cnpj') }}" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">
                <i class="bi bi-search"></i> Consultar Status
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
            <p>Área Administrativa/Operacional: <a href="{{ route('login') }}" style="color: var(--accent-blue); text-decoration: none; font-weight: 600;">Acessar Painel Interno</a></p>
        </div>
    </div>

    @if(isset($tracking))
        <!-- Progress Tracker View -->
        <div class="glass-card">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Status do Rastreamento: #{{ $tracking->order_number }}</h2>

            @php
                $status = $tracking->status;
                // Steps mapping
                // Step 1: pendente_entrega (Aguardando Retirada)
                // Step 2: em_transporte (Em Transporte)
                // Step 3: entregue / enviado_cliente (Entregue / Concluído)
                
                $step = 1;
                if ($status === 'em_transporte') {
                    $step = 2;
                } elseif ($status === 'entregue' || $status === 'enviado_cliente') {
                    $step = 3;
                }
            @endphp

            <div class="timeline-tracker">
                <div class="step-list">
                    <!-- Progress Bar Line -->
                    <div class="step-progress-bar" style="width: @if($step === 1) 16.66% @elseif($step === 2) 50% @else 100% @endif;"></div>

                    <!-- Step 1: Preparation -->
                    <div class="step-item @if($step > 1) completed @elseif($step === 1) active @endif">
                        <div class="step-circle">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="step-label">Preparação</div>
                    </div>

                    <!-- Step 2: In Transit -->
                    <div class="step-item @if($step > 2) completed @elseif($step === 2) active @endif">
                        <div class="step-circle">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="step-label">Em Transporte</div>
                    </div>

                    <!-- Step 3: Delivered -->
                    <div class="step-item @if($step === 3) completed @endif">
                        <div class="step-circle">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="step-label">Entregue</div>
                    </div>
                </div>
            </div>

            <!-- Details box -->
            <div class="tracking-details-box">
                <h3 style="font-size: 1rem; color: var(--accent-blue); margin-bottom: 0.75rem;">Histórico da Entrega</h3>
                
                @if($tracking->status === 'enviado_cliente')
                    <p style="font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="bi bi-info-circle-fill" style="color: var(--accent-green)"></i> Seu pedido foi enviado através da transportadora <strong>{{ $tracking->carrier_name }}</strong>.</p>
                @endif

                <div style="display: flex; flex-direction: column; gap: 0.50rem; font-size: 0.875rem; margin-top: 1rem;">
                    @foreach($tracking->statusLogs->reverse() as $log)
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 0.5rem;">
                            <span style="font-weight: 600; text-transform: capitalize;">
                                @if($log->status === 'pendente_entrega')
                                    Preparado para envio no estoque
                                @elseif($log->status === 'roteirizado')
                                    Roteirizado pela logística (Aguardando saída)
                                @elseif($log->status === 'em_transporte')
                                    Saiu para entrega
                                @elseif($log->status === 'enviado_cliente')
                                    Coletado pela transportadora
                                @elseif($log->status === 'entregue')
                                    Entregue com sucesso
                                @else
                                    {{ str_replace('_', ' ', $log->status) }}
                                @endif
                            </span>
                            <span style="color: var(--text-secondary);">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                                @if($log->latitude && $log->longitude)
                                    | <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" style="color: var(--accent-blue); text-decoration: none;"><i class="bi bi-geo-alt-fill"></i> Mapa</a>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
