@extends('layouts.app')

@section('title', 'Painel Motorista - Rastreamento')

@section('styles')
<style>
    .motorista-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .scan-box {
        text-align: center;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
        padding: 2rem;
        border-radius: 16px;
        border: 1px solid var(--accent-blue);
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px 0 rgba(56, 189, 248, 0.15);
    }
    .scan-icon {
        font-size: 3rem;
        color: var(--accent-blue);
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
    }
    .task-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
    }
    .task-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
    }
    .task-coleta::before { background: var(--accent-purple); }
    .task-entrega::before { background: var(--accent-blue); }

    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .task-type {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .task-coleta .task-type { color: var(--accent-purple); }
    .task-entrega .task-type { color: var(--accent-blue); }

    .task-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .task-detail {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }
    .task-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1rem;
    }
    .badge-status {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-pendente_coleta { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
    .status-pendente_entrega { background: rgba(56, 189, 248, 0.15); color: var(--accent-blue); }
    .status-em_transporte { background: rgba(192, 132, 252, 0.15); color: var(--accent-purple); }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.08); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endsection

@section('content')
<div class="motorista-container">
    <h1 class="page-title"><i class="bi bi-person-badge-fill"></i> Painel do Motorista</h1>

    <!-- QR scanner card -->
    <div class="glass-card scan-box">
        <div class="scan-icon">
            <i class="bi bi-qr-code-scan"></i>
        </div>
        <h2 style="font-size: 1.1rem; margin-bottom: 1.5rem;">Escanear QR Code</h2>

        <!-- Camera Scanner view -->
        <div id="reader" style="width: 100%; max-width: 100%; border-radius: 12px; margin-bottom: 1.5rem; display: none; overflow: hidden; border: 1px solid var(--border-color); background: rgba(0,0,0,0.4);"></div>

        <button type="button" id="btn-start-camera" class="btn-primary" style="width: 100%; margin-bottom: 1rem;" onclick="startCameraScanner()">
            <i class="bi bi-camera-fill"></i> Iniciar Câmera / Escanear
        </button>
        
        <button type="button" id="btn-stop-camera" class="btn-secondary" style="width: 100%; margin-bottom: 1rem; display: none;" onclick="stopCameraScanner()">
            <i class="bi bi-camera-video-off-fill"></i> Parar Câmera
        </button>

        <form action="{{ route('motorista.scan') }}" method="POST" id="scan-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="latitude" id="driver-lat">
            <input type="hidden" name="longitude" id="driver-lng">
            <input type="hidden" name="qrcode_token" id="qrcode_token" required>
            
            <div id="validation-fields" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1rem;">
                <h4 style="font-size: 1rem; color: var(--accent-blue); margin-bottom: 1rem; text-align: center;"><i class="bi bi-shield-check"></i> Comprovação Obrigatória</h4>
                
                <div class="form-group">
                    <label for="product_code_validation" class="form-label">Validar Código do Produto</label>
                    <input type="text" name="product_code_validation" id="product_code_validation" class="form-control" placeholder="Digite o código do produto para conferência">
                </div>

                <div class="form-group">
                    <label for="photo_product" class="form-label">Foto do Produto (Volume)</label>
                    <input type="file" name="photo_product" id="photo_product" class="form-control" accept="image/*" capture="environment">
                </div>

                <div class="form-group">
                    <label for="photo_invoice" class="form-label">Foto da Nota Fiscal</label>
                    <input type="file" name="photo_invoice" id="photo_invoice" class="form-control" accept="image/*" capture="environment">
                </div>
            </div>

            <!-- Action Button -->
            <button type="submit" id="btn-submit-scan" class="btn-primary" style="width: 100%; margin-top: 1rem; display: none;">
                <i class="bi bi-check-circle-fill"></i> Registrar e Baixar Operação
            </button>
            
            <div id="manual-notice" style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: var(--text-secondary);">
                <i class="bi bi-info-circle"></i> Leitura manual desativada para motoristas. Caso precise dar baixa manual, solicite remotamente à equipe de Logística.
            </div>
        </form>
    </div>

    <!-- Active Tasks -->
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;"><i class="bi bi-list-task"></i> Suas Tarefas Ativas</h2>
    
    @forelse($trackings as $t)
        <div class="task-card task-{{ $t->type }}">
            <div class="task-header">
                <span class="task-type">{{ $t->type }}</span>
                <span class="badge-status status-{{ $t->status }}">{{ str_replace('_', ' ', $t->status) }}</span>
            </div>
            <h3 class="task-title">Pedido: {{ $t->order_number }}</h3>
            
            @if($t->type === 'coleta')
                <div class="task-detail"><strong>Origem:</strong> {{ $t->collection_address }}</div>
                <div class="task-detail"><strong>Data Limite:</strong> {{ $t->collection_schedule->format('d/m/Y H:i') }}</div>
            @else
                <div class="task-detail"><strong>Destino:</strong> {{ $t->collection_address }}</div>
                <div class="task-detail"><strong>Transporte:</strong> Veículo Próprio</div>
                <div class="task-detail"><strong>Veículo:</strong> {{ $t->vehicle_info }}</div>
            @endif
            
            @if($t->observations_origin)
                <div class="task-detail" style="margin-top: 0.5rem; font-style: italic;">"{{ $t->observations_origin }}"</div>
            @endif

            <div class="task-actions">
                <button class="btn-secondary btn-scan-trigger" data-token="{{ $t->qrcode_token }}" data-status="{{ $t->status }}" onclick="prefillScanner('{{ $t->qrcode_token }}', '{{ $t->status }}')" style="font-size: 0.875rem; padding: 0.4rem 0.8rem; flex: 1;">
                    <i class="bi bi-qr-code"></i> Ler QR Code
                </button>
            </div>
        </div>
    @empty
        <div class="glass-card" style="text-align: center; color: var(--text-secondary); padding: 3rem;">
            <i class="bi bi-emoji-smile" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
            Nenhuma tarefa pendente atribuída a você no momento!
        </div>
    @endforelse

    <!-- History -->
    @if($history->isNotEmpty())
        <h2 style="font-size: 1.25rem; margin-top: 2rem; margin-bottom: 1rem;"><i class="bi bi-clock-history"></i> Histórico Recente</h2>
        @foreach($history as $h)
            <div class="task-card task-{{ $h->type }}" style="opacity: 0.7;">
                <div class="task-header">
                    <span class="task-type">{{ $h->type }}</span>
                    <span class="badge-status" style="background: rgba(52, 211, 153, 0.15); color: var(--accent-green);">{{ str_replace('_', ' ', $h->status) }}</span>
                </div>
                <h3 class="task-title">Pedido: {{ $h->order_number }}</h3>
                <div class="task-detail">Concluído em: {{ $h->completion_time ? $h->completion_time->format('d/m/Y H:i') : $h->updated_at->format('d/m/Y H:i') }}</div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrCode;

    function startCameraScanner() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("Acesso à câmera indisponível. Dispositivos móveis (iOS/Android) exigem por segurança que o site utilize conexão segura HTTPS para acessar a câmera.");
            return;
        }

        document.getElementById('reader').style.display = 'block';
        document.getElementById('btn-start-camera').style.display = 'none';
        document.getElementById('btn-stop-camera').style.display = 'block';

        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 15, qrbox: { width: 250, height: 250 } };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
            (decodedText, decodedResult) => {
                stopCameraScanner();
                if (navigator.vibrate) {
                    navigator.vibrate(200);
                }
                
                // Find matching task card scan trigger button to extract status
                const btn = Array.from(document.querySelectorAll('.btn-scan-trigger')).find(b => b.dataset.token === decodedText);
                if (btn) {
                    prefillScanner(decodedText, btn.dataset.status);
                } else {
                    alert("Código QR: " + decodedText + " lido. Este pedido não foi localizado em sua lista de tarefas.");
                }
            },
            (errorMessage) => {
                // Ignore scanning failures
            }
        ).catch((err) => {
            console.error("Erro ao iniciar a câmera: ", err);
            alert("Não foi possível acessar a câmera. Verifique se concedeu as permissões necessárias.");
            stopCameraScanner();
        });
    }

    function stopCameraScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                document.getElementById('reader').style.display = 'none';
                document.getElementById('btn-start-camera').style.display = 'block';
                document.getElementById('btn-stop-camera').style.display = 'none';
            }).catch((err) => {
                console.error("Erro ao parar a câmera: ", err);
            });
        } else {
            document.getElementById('reader').style.display = 'none';
            document.getElementById('btn-start-camera').style.display = 'block';
            document.getElementById('btn-stop-camera').style.display = 'none';
        }
    }

    function prefillScanner(token, currentStatus) {
        document.getElementById('qrcode_token').value = token;
        
        const validationFields = document.getElementById('validation-fields');
        const submitBtn = document.getElementById('btn-submit-scan');
        
        if (currentStatus === 'em_transporte') {
            validationFields.style.display = 'block';
            document.getElementById('product_code_validation').required = true;
            document.getElementById('photo_product').required = true;
            document.getElementById('photo_invoice').required = true;
        } else {
            validationFields.style.display = 'none';
            document.getElementById('product_code_validation').required = false;
            document.getElementById('photo_product').required = false;
            document.getElementById('photo_invoice').required = false;
        }
        
        submitBtn.style.display = 'block';
        
        // Scroll to scanner
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('driver-lat').value = position.coords.latitude;
                document.getElementById('driver-lng').value = position.coords.longitude;
                console.log("Geolocalização capturada:", position.coords.latitude, position.coords.longitude);
            }, function(error) {
                console.warn("Erro ao obter geolocalização:", error.message);
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        }
    });
</script>
@endsection
