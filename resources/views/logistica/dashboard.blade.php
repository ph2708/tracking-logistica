@extends('layouts.app')

@section('title', 'Painel Logística - Controle')

@section('styles')
<style>
    .tabs-nav {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .tab-btn {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .tab-btn.active {
        color: var(--accent-blue);
        border-bottom-color: var(--accent-blue);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .log-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .log-table th, .log-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .log-table th {
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 0.875rem;
    }
    .badge-status {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-pendente_roteirizacao { background: rgba(192, 132, 252, 0.15); color: var(--accent-purple); }
    .status-pendente_coleta { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
    .status-coleta_finalizada { background: rgba(52, 211, 153, 0.15); color: var(--accent-green); }
    .status-pendente_entrega { background: rgba(56, 189, 248, 0.15); color: var(--accent-blue); }
    .status-enviado_cliente { background: rgba(52, 211, 153, 0.15); color: var(--accent-green); }
    .status-em_transporte { background: rgba(192, 132, 252, 0.15); color: var(--accent-purple); }
    .status-entregue { background: rgba(52, 211, 153, 0.15); color: var(--accent-green); }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .modal-content {
        max-width: 500px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<h1 class="page-title"><i class="bi bi-gear-wide-connected"></i> Controle Operacional Logístico</h1>

<!-- Navigation Tabs -->
<div class="tabs-nav">
    <button class="tab-btn active" onclick="switchTab(event, 'tab-coletas')">
        <i class="bi bi-box-arrow-in-down"></i> Coletas (Pedidos de Compra)
    </button>
    <button class="tab-btn" onclick="switchTab(event, 'tab-entregas')">
        <i class="bi bi-box-arrow-up-right"></i> Entregas (Pedidos de Venda)
    </button>
</div>

<!-- Tab Coletas -->
<div id="tab-coletas" class="tab-content active">
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Gerenciamento de Coletas</h2>
        <div style="overflow-x: auto;">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Endereço Coleta</th>
                        <th>Agendamento</th>
                        <th>Motorista Atribuído</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coletas as $c)
                        <tr>
                            <td><strong>{{ $c->order_number }}</strong></td>
                            <td>{{ $c->collection_address }}</td>
                            <td>{{ $c->collection_schedule->format('d/m/Y H:i') }}</td>
                            <td>{{ $c->driver ? $c->driver->name : 'Nenhum' }}</td>
                            <td>
                                <span class="badge-status status-{{ $c->status }}">{{ str_replace('_', ' ', $c->status) }}</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('logistica.qrcode', $c->id) }}" target="_blank" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem;">
                                        <i class="bi bi-qr-code"></i> QR Code
                                    </a>
                                    @if($c->status === 'pendente_roteirizacao')
                                        <button class="btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem;" onclick="openRouteModal({{ $c->id }})">
                                            Roteirizar
                                        </button>
                                    @endif
                                    @if($c->status === 'pendente_coleta' || $c->status === 'em_transporte')
                                        <form action="{{ route('logistica.manual-close', $c->id) }}" method="POST" onsubmit="return confirm('Deseja realmente dar baixa manual nesta coleta?')" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem; border-color: var(--accent-green); color: var(--accent-green); background: rgba(52,211,153,0.05);">
                                                Baixa Manual
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                Nenhuma coleta pendente ou cadastrada no momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Entregas -->
<div id="tab-entregas" class="tab-content">
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Gerenciamento de Entregas</h2>
        <div style="overflow-x: auto;">
            <table class="log-table">
                 <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Transporte</th>
                        <th>Detalhes Transporte</th>
                        <th>Endereço Entrega</th>
                        <th>Anexo NF</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entregas as $e)
                        <tr>
                            <td><strong>{{ $e->order_number }}</strong></td>
                            <td style="text-transform: capitalize;">{{ $e->transport_type }}</td>
                            <td>
                                @if($e->transport_type === 'proprio')
                                    <strong>Motorista:</strong> {{ $e->driver ? $e->driver->name : 'Não definido' }}<br>
                                    <strong>Veículo:</strong> {{ $e->vehicle_info ?? 'Não definido' }}
                                @else
                                    <strong>Transp:</strong> {{ $e->carrier_name ?? 'Não definido' }}<br>
                                    <strong>Peso/Dim:</strong> {{ $e->weight }}kg / {{ $e->dimensions }}
                                @endif
                            </td>
                            <td>{{ $e->collection_address }}</td>
                            <td>
                                @if($e->invoice_path)
                                    <a href="{{ Storage::url($e->invoice_path) }}" target="_blank" style="color: var(--accent-blue); text-decoration: none;"><i class="bi bi-file-earmark-pdf-fill"></i> Nota Fiscal</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge-status status-{{ $e->status }}">{{ str_replace('_', ' ', $e->status) }}</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('logistica.qrcode', $e->id) }}" target="_blank" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem;">
                                        <i class="bi bi-qr-code"></i> QR Code
                                    </a>
                                    @if($e->status === 'pendente_entrega' && (($e->transport_type === 'proprio' && !$e->driver_id) || ($e->transport_type === 'terceirizado' && !$e->carrier_name)))
                                        <button class="btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem;" onclick="openShipModal({{ json_encode($e) }})">
                                            Roteirizar
                                        </button>
                                    @endif
                                    @if(($e->status === 'pendente_entrega' && $e->driver_id) || $e->status === 'em_transporte')
                                        <form action="{{ route('logistica.manual-close', $e->id) }}" method="POST" onsubmit="return confirm('Deseja realmente dar baixa manual nesta entrega?')" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem; border-color: var(--accent-green); color: var(--accent-green); background: rgba(52,211,153,0.05);">
                                                Baixa Manual
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                Nenhuma entrega pendente ou enviada no momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Roteirizar Coleta Modal -->
<div id="route-modal" class="modal">
    <div class="glass-card modal-content">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Atribuir Motorista (Roteirização)</h2>
        <form action="{{ route('logistica.route') }}" method="POST">
            @csrf
            <input type="hidden" name="tracking_id" id="route-tracking-id">
            
            <div class="form-group">
                <label for="driver_id" class="form-label">Selecionar Motorista/Técnico</label>
                <select name="driver_id" id="driver_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="observations_logistics" class="form-label">Observações da Logística</label>
                <textarea name="observations_logistics" id="observations_logistics" class="form-control" rows="3" placeholder="Instruções de rota ou restrições"></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1;">Salvar Roteirização</button>
                <button type="button" class="btn-secondary" onclick="closeRouteModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Roteirizar Entrega Modal -->
<div id="ship-modal" class="modal">
    <div class="glass-card modal-content">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Roteirizar Entrega (Definir Transporte)</h2>
        <form action="{{ route('logistica.ship') }}" method="POST">
            @csrf
            <input type="hidden" name="tracking_id" id="ship-tracking-id">
            
            <div id="ship-proprio-fields" style="display: none;">
                <div class="form-group">
                    <label for="ship_driver_id" class="form-label">Selecionar Motorista</label>
                    <select name="driver_id" id="ship_driver_id" class="form-control">
                        <option value="">Selecione...</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="vehicle_info" class="form-label">Informações do Veículo (Placa / Modelo)</label>
                    <input type="text" name="vehicle_info" id="vehicle_info" class="form-control" placeholder="Ex: Placa ABC-1234 / FIAT DUCATO">
                </div>
            </div>

            <div id="ship-terceirizado-fields" style="display: none;">
                <div class="form-group">
                    <label for="carrier_name" class="form-label">Nome da Transportadora</label>
                    <input type="text" name="carrier_name" id="carrier_name" class="form-control" placeholder="Ex: Braspress / Jadlog">
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1;">Despachar / Confirmar</button>
                <button type="button" class="btn-secondary" onclick="closeShipModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(evt, tabId) {
        // Hide all contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.remove('active'));

        // Deactivate all buttons
        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => btn.classList.remove('active'));

        // Show active tab
        document.getElementById(tabId).classList.add('active');
        evt.currentTarget.classList.add('active');
    }

    function openRouteModal(trackingId) {
        document.getElementById('route-tracking-id').value = trackingId;
        document.getElementById('route-modal').style.display = 'flex';
    }

    function closeRouteModal() {
        document.getElementById('route-modal').style.display = 'none';
    }

    function openShipModal(tracking) {
        document.getElementById('ship-tracking-id').value = tracking.id;
        
        const proprioFields = document.getElementById('ship-proprio-fields');
        const terceirizadoFields = document.getElementById('ship-terceirizado-fields');
        
        if (tracking.transport_type === 'proprio') {
            proprioFields.style.display = 'block';
            terceirizadoFields.style.display = 'none';
            document.getElementById('ship_driver_id').required = true;
            document.getElementById('vehicle_info').required = true;
            document.getElementById('carrier_name').required = false;
        } else {
            proprioFields.style.display = 'none';
            terceirizadoFields.style.display = 'block';
            document.getElementById('ship_driver_id').required = false;
            document.getElementById('vehicle_info').required = false;
            document.getElementById('carrier_name').required = true;
        }
        
        document.getElementById('ship-modal').style.display = 'flex';
    }

    // Close modals on clicking background
    window.onclick = function(event) {
        const routeModal = document.getElementById('route-modal');
        const shipModal = document.getElementById('ship-modal');
        if (event.target == routeModal) {
            closeRouteModal();
        }
        if (event.target == shipModal) {
            closeShipModal();
        }
    }

    function closeShipModal() {
        document.getElementById('ship-modal').style.display = 'none';
    }
</script>
@endsection
