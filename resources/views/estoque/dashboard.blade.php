@extends('layouts.app')

@section('title', 'Painel Estoque - Rastreamento')

@section('styles')
<style>
    .stock-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    .order-details-card {
        display: none;
        margin-top: 1.5rem;
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.875rem;
    }
    .items-table th, .items-table td {
        padding: 0.5rem 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .items-table th {
        color: var(--text-secondary);
    }
    .dynamic-fields {
        display: none;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }
    .tracking-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .tracking-list-table th, .tracking-list-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .tracking-list-table th {
        color: var(--text-secondary);
        font-size: 0.875rem;
        text-transform: uppercase;
    }
    .badge-status {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-pendente_entrega { background: rgba(56, 189, 248, 0.15); color: var(--accent-blue); }
    .status-finalizada { background: rgba(52, 211, 153, 0.15); color: var(--accent-green); }

    @media (max-width: 992px) {
        .stock-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<h1 class="page-title"><i class="bi bi-box-seam-fill"></i> Painel do Estoque</h1>

<div class="stock-grid">
    <!-- Search & Register Tracking -->
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Iniciar Rastreamento de Pedido de Venda</h2>
        
        <!-- Search bar -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
            <input type="text" id="search-order-input" class="form-control" placeholder="Número do Pedido de Venda Protheus">
            <button class="btn-primary" onclick="searchSalesOrder()">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>

        <div id="search-loading" style="display: none; text-align: center; margin: 2rem 0;">
            <div style="color: var(--accent-blue); font-size: 1.5rem;"><i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite; display: inline-block;"></i></div>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.5rem;">Consultando banco Protheus...</p>
        </div>

        <!-- Form for registration -->
        <form action="{{ route('estoque.store') }}" method="POST" enctype="multipart/form-data" id="tracking-form">
            @csrf
            <input type="hidden" name="order_number" id="form-order-number">

            <div class="order-details-card" id="order-details">
                <h3 style="font-size: 1rem; color: var(--accent-blue); margin-bottom: 0.75rem;">Detalhes do Pedido <span id="mock-badge" class="badge-status" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; margin-left: 0.5rem; display: none;">SIMULADO</span></h3>
                <p style="font-size: 0.875rem;"><strong>Cliente:</strong> <span id="order-client"></span> (Loja: <span id="order-store"></span>)</p>
                <p style="font-size: 0.875rem;"><strong>Emissão:</strong> <span id="order-date"></span></p>

                <h4 style="font-size: 0.875rem; margin-top: 1rem; margin-bottom: 0.5rem;">Itens do Pedido:</h4>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Descrição</th>
                                <th>Qtd</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody id="order-items-body"></tbody>
                    </table>
                </div>

                <div style="margin-top: 1.5rem;">
                    <!-- Endereço de Entrega fields -->
                    <h4 style="font-size: 1rem; color: var(--accent-blue); margin-top: 1.5rem; margin-bottom: 1rem;">Endereço de Entrega</h4>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="delivery_cep" class="form-label">CEP</label>
                            <input type="text" name="delivery_cep" id="delivery_cep" class="form-control" placeholder="00000-000" onkeyup="lookupCep(this.value)" required>
                        </div>
                        <div class="form-group">
                            <label for="delivery_street" class="form-label">Rua / Logradouro</label>
                            <input type="text" name="delivery_street" id="delivery_street" class="form-control" placeholder="Ex: Av. Brasil" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="delivery_number" class="form-label">Número</label>
                            <input type="text" name="delivery_number" id="delivery_number" class="form-control" placeholder="Ex: 123 ou S/N" required>
                        </div>
                        <div class="form-group">
                            <label for="delivery_neighborhood" class="form-label">Bairro</label>
                            <input type="text" name="delivery_neighborhood" id="delivery_neighborhood" class="form-control" placeholder="Bairro" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="delivery_city" class="form-label">Cidade</label>
                            <input type="text" name="delivery_city" id="delivery_city" class="form-control" placeholder="Cidade" required>
                        </div>
                        <div class="form-group">
                            <label for="delivery_state" class="form-label">UF</label>
                            <input type="text" name="delivery_state" id="delivery_state" class="form-control" placeholder="Ex: SP" maxlength="2" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observations_origin" class="form-label">Observações do Estoque</label>
                        <textarea name="observations_origin" id="observations_origin" class="form-control" rows="2" placeholder="Observações de embalagem, volumes, etc."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="transport_type" class="form-label">Tipo de Transporte</label>
                        <select name="transport_type" id="transport_type" class="form-control" onchange="toggleTransportFields()" required>
                            <option value="">Selecione...</option>
                            <option value="proprio">Veículo Próprio</option>
                            <option value="terceirizado">Terceirizado (Transpostadora)</option>
                        </select>
                    </div>

                    <!-- Own Vehicle Fields -->
                    <div id="fields-proprio" class="dynamic-fields">
                        <div class="form-group">
                            <label for="departure_time" class="form-label">Data e Hora Planejada de Saída</label>
                            <input type="datetime-local" name="departure_time" id="departure_time" class="form-control">
                        </div>
                    </div>

                    <!-- Third Party Fields -->
                    <div id="fields-terceirizado" class="dynamic-fields">
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="weight" class="form-label">Peso Bruto (KG)</label>
                                <input type="number" step="0.01" name="weight" id="weight" class="form-control" placeholder="Ex: 120.50">
                            </div>
                            <div class="form-group">
                                <label for="dimensions" class="form-label">Dimensões (Volume)</label>
                                <input type="text" name="dimensions" id="dimensions" class="form-control" placeholder="Ex: 1.2x0.8x1.0m">
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="value" class="form-label">Valor Declarado (R$)</label>
                                <input type="number" step="0.01" name="value" id="value" class="form-control" placeholder="Ex: 5400.00">
                            </div>
                            <div class="form-group">
                                <label for="invoice_file" class="form-label">Anexo de Nota Fiscal (PDF/Imagem)</label>
                                <input type="file" name="invoice_file" id="invoice_file" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="bi bi-check-circle-fill"></i> Iniciar Rastreamento & Gerar QR Code
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Registered trackings list -->
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Entregas Recentes Iniciadas</h2>
        
        <div style="overflow-x: auto;">
            <table class="tracking-list-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Tipo Transp.</th>
                        <th>Token QR</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackings as $t)
                        <tr>
                            <td><strong>{{ $t->order_number }}</strong></td>
                            <td style="text-transform: capitalize;">{{ $t->transport_type }}</td>
                            <td style="font-family: monospace;">{{ $t->qrcode_token }}</td>
                            <td>
                                <span class="badge-status status-{{ $t->status }}">{{ str_replace('_', ' ', $t->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                Nenhuma entrega registrada recentemente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('scripts')
<script>
    function searchSalesOrder() {
        const orderNumber = document.getElementById('search-order-input').value.trim();
        if (!orderNumber) {
            alert('Por favor, informe o número do pedido de venda.');
            return;
        }

        document.getElementById('search-loading').style.display = 'block';
        document.getElementById('order-details').style.display = 'none';

        fetch(`/estoque/search?order_number=${orderNumber}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('search-loading').style.display = 'none';
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Fill form
                document.getElementById('form-order-number').value = data.order_number;
                document.getElementById('order-client').innerText = data.client_code;
                document.getElementById('order-store').innerText = data.client_store;
                
                // Format date from YYYYMMDD to DD/MM/YYYY
                let dateStr = data.emission_date;
                if (dateStr && dateStr.length === 8) {
                    dateStr = `${dateStr.substring(6,8)}/${dateStr.substring(4,6)}/${dateStr.substring(0,4)}`;
                }
                document.getElementById('order-date').innerText = dateStr;

                // Show simulated badge
                if (data.is_mock) {
                    document.getElementById('mock-badge').style.display = 'inline-block';
                } else {
                    document.getElementById('mock-badge').style.display = 'none';
                }

                // Render items
                const tbody = document.getElementById('order-items-body');
                tbody.innerHTML = '';
                data.items.forEach(item => {
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${item.product}</strong></td>
                            <td>${item.description}</td>
                            <td>${item.quantity}</td>
                            <td>R$ ${item.value.toFixed(2)}</td>
                        </tr>
                    `;
                });

                document.getElementById('order-details').style.display = 'block';
            })
            .catch(err => {
                document.getElementById('search-loading').style.display = 'none';
                alert('Erro ao consultar o Protheus. Tente novamente.');
                console.error(err);
            });
    }

    function toggleTransportFields() {
        const type = document.getElementById('transport_type').value;
        const fieldsProprio = document.getElementById('fields-proprio');
        const fieldsTerceirizado = document.getElementById('fields-terceirizado');

        if (type === 'proprio') {
            fieldsProprio.style.display = 'block';
            fieldsTerceirizado.style.display = 'none';
            document.getElementById('departure_time').required = true;
            document.getElementById('weight').required = false;
            document.getElementById('dimensions').required = false;
            document.getElementById('value').required = false;
        } else if (type === 'terceirizado') {
            fieldsProprio.style.display = 'none';
            fieldsTerceirizado.style.display = 'block';
            document.getElementById('departure_time').required = false;
            document.getElementById('weight').required = true;
            document.getElementById('dimensions').required = true;
            document.getElementById('value').required = true;
        } else {
            fieldsProprio.style.display = 'none';
            fieldsTerceirizado.style.display = 'none';
        }
    }

    function lookupCep(val) {
        const cep = val.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('delivery_street').value = data.logradouro;
                        document.getElementById('delivery_neighborhood').value = data.bairro;
                        document.getElementById('delivery_city').value = data.localidade;
                        document.getElementById('delivery_state').value = data.uf;
                        document.getElementById('delivery_number').focus();
                    } else {
                        alert('CEP não encontrado.');
                    }
                })
                .catch(err => console.error('Erro ao buscar CEP:', err));
        }
    }
</script>
@endsection
