@extends('layouts.app')

@section('title', 'Diretoria Dashboard - Métricas')

@section('styles')
<style>
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .metric-card {
        padding: 1.5rem;
        text-align: center;
    }
    .metric-value {
        font-size: 2.25rem;
        font-weight: 700;
        margin: 0.5rem 0;
        background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .metric-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
    }
    .operation-details {
        background: rgba(15, 23, 42, 0.4);
        padding: 1rem;
        border-radius: 8px;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        border: 1px solid var(--border-color);
        display: none;
    }
    .btn-toggle-logs {
        background: transparent;
        border: none;
        color: var(--accent-blue);
        cursor: pointer;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-weight: 600;
    }
    .btn-toggle-logs:hover {
        color: #7dd3fc;
    }
    .timeline {
        margin-top: 1rem;
        padding-left: 1rem;
        border-left: 2px solid var(--border-color);
    }
    .timeline-item {
        margin-bottom: 0.75rem;
        position: relative;
        padding-left: 1rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.4rem;
        top: 0.25rem;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent-blue);
    }
    .timeline-time {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .timeline-status {
        font-weight: 600;
        text-transform: capitalize;
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

    .table-diretoria {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .table-diretoria th, .table-diretoria td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .table-diretoria th {
        color: var(--text-secondary);
        font-size: 0.875rem;
        text-transform: uppercase;
    }

    @media (max-width: 992px) {
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<h1 class="page-title"><i class="bi bi-bar-chart-line-fill"></i> Painel da Diretoria</h1>

<!-- Metrics -->
<div class="metrics-grid">
    <div class="glass-card metric-card">
        <div class="metric-label">Entregas Ativas</div>
        <div class="metric-value">{{ $activeDeliveries }}</div>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">Total: {{ $totalDeliveries }}</div>
    </div>
    
    <div class="glass-card metric-card">
        <div class="metric-label">Coletas Ativas</div>
        <div class="metric-value">{{ $activeCollections }}</div>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">Total: {{ $totalCollections }}</div>
    </div>

    <div class="glass-card metric-card">
        <div class="metric-label">Concluídos Hoje</div>
        <div class="metric-value">{{ $completedToday }}</div>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">Entrega & Coleta</div>
    </div>

    <div class="glass-card metric-card">
        <div class="metric-label">Eficiência Geral</div>
        <div class="metric-value">
            @if(($totalDeliveries + $totalCollections) > 0)
                {{ round((($totalDeliveries - $activeDeliveries) + ($totalCollections - $activeCollections)) / ($totalDeliveries + $totalCollections) * 100) }}%
            @else
                100%
            @endif
        </div>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">Taxa de operações concluídas</div>
    </div>
</div>

<!-- Operations List -->
<div class="glass-card">
    <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Visão Consolidada de Operações</h2>
    
    <div style="overflow-x: auto;">
        <table class="table-diretoria">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Operação</th>
                    <th>Transporte/Motorista</th>
                    <th>QR Token</th>
                    <th>Última Atualização</th>
                    <th>Status</th>
                    <th>Histórico</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trackings as $t)
                    <tr>
                        <td><strong>{{ $t->order_number }}</strong></td>
                        <td style="text-transform: uppercase; font-size: 0.8rem; font-weight: 600;">
                            @if($t->type === 'entrega')
                                <span style="color: var(--accent-blue);"><i class="bi bi-box-arrow-up-right"></i> Entrega</span>
                            @else
                                <span style="color: var(--accent-purple);"><i class="bi bi-box-arrow-in-down"></i> Coleta</span>
                            @endif
                        </td>
                        <td>
                            @if($t->transport_type === 'proprio')
                                <i class="bi bi-person-fill"></i> {{ $t->driver ? $t->driver->name : 'Não definido' }}
                            @elseif($t->transport_type === 'terceirizado')
                                <i class="bi bi-building"></i> {{ $t->carrier_name }}
                            @else
                                <i class="bi bi-hourglass-split"></i> Roteirizando
                            @endif
                        </td>
                        <td style="font-family: monospace;">{{ $t->qrcode_token }}</td>
                        <td>{{ $t->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge-status status-{{ $t->status }}">{{ str_replace('_', ' ', $t->status) }}</span>
                        </td>
                        <td>
                            <button class="btn-toggle-logs" onclick="toggleLogs({{ $t->id }})">
                                <i class="bi bi-eye"></i> Detalhes
                            </button>
                        </td>
                    </tr>
                    <tr id="logs-row-{{ $t->id }}" style="display: none; background: rgba(0, 0, 0, 0.1);">
                        <td colspan="7">
                            <div class="operation-details" id="details-{{ $t->id }}" style="display: block;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                                    <div>
                                        <h4 style="margin-bottom: 0.5rem; color: var(--accent-blue);">Informações Operacionais</h4>
                                        <p><strong>Observações Origem:</strong> {{ $t->observations_origin ?? 'Nenhuma' }}</p>
                                        <p><strong>Observações Logística:</strong> {{ $t->observations_logistics ?? 'Nenhuma' }}</p>
                                        @if($t->collection_address)
                                            <p><strong>Endereço de Coleta:</strong> {{ $t->collection_address }}</p>
                                        @endif
                                        @if($t->departure_time)
                                            <p><strong>Horário de Saída:</strong> {{ $t->departure_time->format('d/m/Y H:i') }}</p>
                                        @endif
                                        @if($t->completion_time)
                                            <p><strong>Horário de Conclusão:</strong> {{ $t->completion_time->format('d/m/Y H:i') }}</p>
                                        @endif
                                        @if($t->delivery_photo_product)
                                            <p><strong>Foto do Produto:</strong> <a href="{{ Storage::url($t->delivery_photo_product) }}" target="_blank" style="color: var(--accent-blue); text-decoration: none;"><i class="bi bi-image"></i> Ver Foto</a></p>
                                        @endif
                                        @if($t->delivery_photo_invoice)
                                            <p><strong>Foto da Nota Fiscal:</strong> <a href="{{ Storage::url($t->delivery_photo_invoice) }}" target="_blank" style="color: var(--accent-blue); text-decoration: none;"><i class="bi bi-image"></i> Ver Foto</a></p>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 style="margin-bottom: 0.5rem; color: var(--accent-blue);">Linha do Tempo (Logs de Status)</h4>
                                        <div class="timeline">
                                            @foreach($t->statusLogs as $log)
                                                <div class="timeline-item">
                                                    <div class="timeline-status">
                                                        @if($log->status === 'pendente_roteirizacao')
                                                            Solicitado pelo Compras (Aguardando Roteirização)
                                                        @elseif($log->status === 'pendente_coleta')
                                                            Coleta Roteirizada pela Logística (Aguardando Coleta)
                                                        @elseif($log->status === 'roteirizado')
                                                            Entrega Roteirizada pela Logística (Aguardando Saída)
                                                        @elseif($log->status === 'pendente_entrega')
                                                            Registrado no Estoque
                                                        @elseif($log->status === 'em_transporte')
                                                            Saiu para Transporte
                                                        @elseif($log->status === 'coleta_finalizada')
                                                            Coleta Finalizada na Empresa
                                                        @elseif($log->status === 'entregue')
                                                            Entregue no Cliente
                                                        @elseif($log->status === 'enviado_cliente')
                                                            Enviado p/ Cliente via Transportadora
                                                        @else
                                                            {{ str_replace('_', ' ', $log->status) }}
                                                        @endif
                                                    </div>
                                                    <div class="timeline-time">
                                                        {{ $log->created_at->format('d/m/Y H:i:s') }} 
                                                        @if($log->user)
                                                            por {{ $log->user->name }} ({{ $log->user->role }})
                                                        @endif
                                                        @if($log->latitude && $log->longitude)
                                                            <br><a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" style="color: var(--accent-blue); text-decoration: none;"><i class="bi bi-geo-alt-fill"></i> Ver no Mapa</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 3rem;">
                            Nenhuma operação registrada no sistema.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleLogs(id) {
        const row = document.getElementById(`logs-row-${id}`);
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }
</script>
@endsection
