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

    <!-- Scanner Modal (Hidden by default) -->
    <div id="scanner-modal" class="modal" style="display: none;">
        <div class="glass-card modal-content" style="max-width: 500px; width: 90%; padding: 1.5rem; margin: auto; position: relative;">
            <h3 id="modal-task-title" style="font-size: 1.1rem; color: var(--accent-blue); margin-bottom: 1rem;"><i class="bi bi-qr-code-scan"></i> Atendimento de Pedido</h3>
            
            <!-- Dynamic Alert Message inside Modal -->
            <div id="modal-alert" class="alert alert-danger" style="display: none; margin-bottom: 1rem; padding: 0.75rem 1rem; font-size: 0.85rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="modal-alert-message"></span>
            </div>

            <!-- Camera View -->
            <div id="modal-reader" style="width: 100%; max-width: 100%; border-radius: 12px; margin-bottom: 1rem; overflow: hidden; border: 1px solid var(--border-color); background: rgba(0,0,0,0.4); display: none;"></div>
            
            <div id="camera-instructions" style="text-align: center; margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
                <p>Clique abaixo para abrir a câmera e escanear o QR Code de confirmação.</p>
            </div>

            <button type="button" id="btn-modal-start-camera" class="btn-primary" style="width: 100%; margin-bottom: 1rem;" onclick="startModalCamera()">
                <i class="bi bi-camera-fill"></i> Abrir Câmera & Escanear
            </button>
            <button type="button" id="btn-modal-stop-camera" class="btn-secondary" style="width: 100%; margin-bottom: 1rem; display: none;" onclick="stopModalCamera()">
                <i class="bi bi-camera-video-off-fill"></i> Parar Câmera
            </button>

            <!-- Form -->
            <form action="{{ route('motorista.scan') }}" method="POST" id="scan-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="driver-lat">
                <input type="hidden" name="longitude" id="driver-lng">
                <input type="hidden" name="qrcode_token" id="qrcode_token">

                <!-- Verification Message (shown when QR code matches) -->
                <div id="qr-matched-success" class="alert alert-success" style="display: none; margin-bottom: 1rem; padding: 0.75rem 1rem; font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>QR Code verificado com sucesso!</span>
                </div>

                <!-- Validation Fields (shown for em_transporte state) -->
                <div id="validation-fields" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
                    <h4 style="font-size: 0.9rem; color: var(--accent-blue); margin-bottom: 1rem; text-align: center;"><i class="bi bi-shield-check"></i> Comprovação Obrigatória</h4>
                    
                    <div class="form-group">
                        <label for="product_code_validation" class="form-label">Código de Produto para Conferência</label>
                        <input type="text" name="product_code_validation" id="product_code_validation" class="form-control" placeholder="Digite o código do produto para conferência">
                    </div>

                    <div class="form-group">
                        <label for="photo_invoice" class="form-label">Foto da Nota Fiscal</label>
                        <input type="file" name="photo_invoice" id="photo_invoice" class="form-control" accept="image/*" capture="environment">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fotos do Produto (Volume) - Até 3 fotos</label>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <input type="file" name="photo_product" id="photo_product" class="form-control" accept="image/*" capture="environment" placeholder="Foto 1 (Obrigatória)">
                            <input type="file" name="photo_product_2" id="photo_product_2" class="form-control" accept="image/*" capture="environment" placeholder="Foto 2 (Opcional)">
                            <input type="file" name="photo_product_3" id="photo_product_3" class="form-control" accept="image/*" capture="environment" placeholder="Foto 3 (Opcional)">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btn-submit-scan" class="btn-primary" style="width: 100%; margin-top: 1rem; display: none;">
                    <i class="bi bi-check-circle-fill"></i> Confirmar Operação
                </button>
            </form>

            <button type="button" class="btn-secondary" style="width: 100%; margin-top: 0.5rem;" onclick="closeScannerModal()">
                Cancelar / Fechar
            </button>
        </div>
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
                <button class="btn-secondary btn-scan-trigger" data-token="{{ $t->qrcode_token }}" data-status="{{ $t->status }}" onclick="openScannerModal('{{ $t->qrcode_token }}', '{{ $t->status }}', '{{ $t->order_number }}')" style="font-size: 0.875rem; padding: 0.4rem 0.8rem; flex: 1;">
                    <i class="bi bi-qr-code"></i> Iniciar Atendimento
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
    window.targetToken = null;
    window.targetStatus = null;
    window.targetOrderNumber = null;

    function showModalAlert(message) {
        const alertEl = document.getElementById('modal-alert');
        const messageEl = document.getElementById('modal-alert-message');
        messageEl.innerText = message;
        alertEl.style.display = 'flex';
    }

    function hideModalAlert() {
        document.getElementById('modal-alert').style.display = 'none';
    }

    function openScannerModal(token, status, orderNumber) {
        window.targetToken = token;
        window.targetStatus = status;
        window.targetOrderNumber = orderNumber;

        document.getElementById('modal-task-title').innerHTML = `<i class="bi bi-qr-code-scan"></i> Pedido ${orderNumber}`;
        document.getElementById('qrcode_token').value = '';
        
        // Hide success message, form fields and submit button initially
        document.getElementById('qr-matched-success').style.display = 'none';
        document.getElementById('validation-fields').style.display = 'none';
        document.getElementById('btn-submit-scan').style.display = 'none';
        
        hideModalAlert();

        // Show start camera button and instructions
        document.getElementById('btn-modal-start-camera').style.display = 'block';
        document.getElementById('camera-instructions').style.display = 'block';
        document.getElementById('btn-modal-stop-camera').style.display = 'none';
        document.getElementById('modal-reader').style.display = 'none';

        // Display modal
        document.getElementById('scanner-modal').style.display = 'flex';
    }

    function closeScannerModal() {
        stopModalCamera();
        document.getElementById('scanner-modal').style.display = 'none';
    }

    function startModalCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showAlertGlobal("Acesso à câmera indisponível. Dispositivos móveis exigem HTTPS por segurança.");
            return;
        }

        document.getElementById('modal-reader').style.display = 'block';
        document.getElementById('btn-modal-start-camera').style.display = 'none';
        document.getElementById('btn-modal-stop-camera').style.display = 'block';
        document.getElementById('camera-instructions').style.display = 'none';

        hideModalAlert();

        html5QrCode = new Html5Qrcode("modal-reader");
        const config = { fps: 15, qrbox: { width: 220, height: 220 } };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
            (decodedText, decodedResult) => {
                if (decodedText.trim() === window.targetToken.trim()) {
                    // Match!
                    stopModalCamera();
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                    handleQRMatchSuccess();
                } else {
                    showModalAlert("Código QR incorreto! O código escaneado não corresponde ao pedido selecionado.");
                }
            },
            (errorMessage) => {
                // Ignore scanning failures
            }
        ).catch((err) => {
            console.error("Erro ao iniciar a câmera: ", err);
            showModalAlert("Não foi possível acessar a câmera. Verifique as permissões de vídeo.");
            stopModalCamera();
        });
    }

    function stopModalCamera() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                document.getElementById('modal-reader').style.display = 'none';
                document.getElementById('btn-modal-start-camera').style.display = 'block';
                document.getElementById('btn-modal-stop-camera').style.display = 'none';
            }).catch((err) => {
                console.error("Erro ao parar a câmera: ", err);
            });
        } else {
            document.getElementById('modal-reader').style.display = 'none';
            document.getElementById('btn-modal-start-camera').style.display = 'block';
            document.getElementById('btn-modal-stop-camera').style.display = 'none';
        }
    }

    function handleQRMatchSuccess() {
        document.getElementById('qrcode_token').value = window.targetToken;
        document.getElementById('qr-matched-success').style.display = 'flex';
        
        // Hide initial instructions and buttons
        document.getElementById('btn-modal-start-camera').style.display = 'none';
        document.getElementById('btn-modal-stop-camera').style.display = 'none';
        document.getElementById('camera-instructions').style.display = 'none';

        const validationFields = document.getElementById('validation-fields');
        const submitBtn = document.getElementById('btn-submit-scan');

        if (window.targetStatus === 'em_transporte') {
            validationFields.style.display = 'block';
            document.getElementById('product_code_validation').required = true;
            document.getElementById('photo_product').required = true;
            document.getElementById('photo_invoice').required = true;
            
            // Photo 2 and 3 are optional, no required attribute
        } else {
            validationFields.style.display = 'none';
            document.getElementById('product_code_validation').required = false;
            document.getElementById('photo_product').required = false;
            document.getElementById('photo_invoice').required = false;
        }

        submitBtn.style.display = 'block';
    }

    function showAlertGlobal(msg) {
        alert(msg);
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
