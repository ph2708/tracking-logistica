@extends('layouts.app')

@section('title', 'Painel Compras - Solicitar Coleta')

@section('styles')
<style>
    .compras-grid {
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
    .status-pendente_roteirizacao { background: rgba(192, 132, 252, 0.15); color: var(--accent-purple); }
    .status-pendente_coleta { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
    .status-coleta_finalizada { background: rgba(52, 211, 153, 0.15); color: var(--accent-green); }

    @media (max-width: 992px) {
        .compras-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<h1 class="page-title"><i class="bi bi-cart-fill"></i> Painel do Comprador</h1>

<div class="compras-grid">
    <!-- Search & Register Collection -->
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Solicitar Coleta de Pedido de Compra</h2>
        
        <!-- Dynamic Alert Message -->
        <div id="dynamic-alert" class="alert alert-danger" style="display: none; margin-bottom: 1.5rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span id="dynamic-alert-message"></span>
        </div>

        <!-- Search bar -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
            <input type="text" id="search-order-input" class="form-control" placeholder="Número do Pedido de Compra Protheus">
            <button class="btn-primary" onclick="searchPurchaseOrder()">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>

        <div id="search-loading" style="display: none; text-align: center; margin: 2rem 0;">
            <div style="color: var(--accent-blue); font-size: 1.5rem;"><i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite; display: inline-block;"></i></div>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.5rem;">Consultando banco Protheus...</p>
        </div>

        <!-- Form for registration -->
        <form action="{{ route('compras.store') }}" method="POST" id="tracking-form">
            @csrf
            <input type="hidden" name="order_number" id="form-order-number">
            <input type="hidden" name="branch" id="form-branch">

            <div class="order-details-card" id="order-details">
                <h3 style="font-size: 1rem; color: var(--accent-blue); margin-bottom: 0.75rem;">Detalhes do Pedido <span id="mock-badge" class="badge-status" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; margin-left: 0.5rem; display: none;">SIMULADO</span></h3>
                
                <!-- Dynamic Branch Selector for Multiple Branches -->
                <div id="branch-selector-container" style="margin-bottom: 1.25rem; padding: 0.75rem; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 8px; display: none;">
                    <label for="order-branch-select" class="form-label" style="color: var(--accent-blue); font-weight: bold; margin-bottom: 0.5rem; display: block;">
                        <i class="bi bi-info-circle-fill"></i> Múltiplas filiais localizadas neste pedido. Filtrar por:
                    </label>
                    <select id="order-branch-select" class="form-control" style="background-color: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color);" onchange="filterBranchItems(this.value)">
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <p style="font-size: 0.875rem;"><strong>Filial Selecionada:</strong> <span id="order-branch-text" style="font-weight: bold; color: var(--accent-blue);"></span></p>
                <p style="font-size: 0.875rem;"><strong>Fornecedor:</strong> <span id="order-supplier"></span> (Loja: <span id="order-store"></span>)</p>
                <p style="font-size: 0.875rem;"><strong>Emissão:</strong> <span id="order-date"></span></p>

                <h4 style="font-size: 0.875rem; margin-top: 1rem; margin-bottom: 0.5rem;">Itens do Pedido:</h4>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Descrição</th>
                                <th>Filial</th>
                                <th>Qtd</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody id="order-items-body"></tbody>
                    </table>
                </div>

                <div style="margin-top: 1.5rem;">
                    <!-- CEP and Auto-fill -->
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="collection_cep" class="form-label">CEP</label>
                            <input type="text" name="collection_cep" id="collection_cep" class="form-control" placeholder="00000-000" onkeyup="lookupCep(this.value)" required>
                        </div>
                        <div class="form-group">
                            <label for="collection_street" class="form-label">Rua / Logradouro</label>
                            <input type="text" name="collection_street" id="collection_street" class="form-control" placeholder="Ex: Av. Brasil" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="collection_number" class="form-label">Número</label>
                            <input type="text" name="collection_number" id="collection_number" class="form-control" placeholder="Ex: 123 ou S/N" required>
                        </div>
                        <div class="form-group">
                            <label for="collection_neighborhood" class="form-label">Bairro</label>
                            <input type="text" name="collection_neighborhood" id="collection_neighborhood" class="form-control" placeholder="Bairro" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="collection_city" class="form-label">Cidade</label>
                            <input type="text" name="collection_city" id="collection_city" class="form-control" placeholder="Cidade" required>
                        </div>
                        <div class="form-group">
                            <label for="collection_state" class="form-label">UF</label>
                            <input type="text" name="collection_state" id="collection_state" class="form-control" placeholder="Ex: SP" maxlength="2" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="collection_schedule" class="form-label">Data e Hora da Coleta</label>
                        <input type="datetime-local" name="collection_schedule" id="collection_schedule" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="observations_origin" class="form-label">Observações da Coleta</label>
                        <textarea name="observations_origin" id="observations_origin" class="form-control" rows="3" placeholder="Informações de manuseio, contatos ou observações gerais de recebimento"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="bi bi-send-fill"></i> Solicitar Coleta & Enviar para Logística
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Solicitações de Coleta Recentes -->
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Coletas Solicitadas</h2>
        
        <div style="overflow-x: auto;">
            <table class="tracking-list-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Filial</th>
                        <th>Agendado Para</th>
                        <th>Token QR</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackings as $t)
                        <tr>
                            <td>
                                <button type="button" class="btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; font-weight: 700; border-color: rgba(56, 189, 248, 0.3); color: var(--accent-blue);" onclick="selectRecentOrder('{{ $t->order_number }}', '{{ $t->branch }}')" title="Selecionar para consulta">
                                    {{ $t->order_number }}
                                </button>
                            </td>
                            <td>{{ $t->branch ?? 'Todas' }}</td>
                            <td>{{ $t->collection_schedule->format('d/m/Y H:i') }}</td>
                            <td style="font-family: monospace;">{{ $t->qrcode_token }}</td>
                            <td>
                                <span class="badge-status status-{{ $t->status }}">{{ str_replace('_', ' ', $t->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                                Nenhuma solicitação de coleta registrada recentemente.
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
    let currentOrderItems = [];
    window.pendingSelectedBranch = null;

    function showAlert(message) {
        const alertEl = document.getElementById('dynamic-alert');
        const messageEl = document.getElementById('dynamic-alert-message');
        messageEl.innerText = message;
        alertEl.style.display = 'flex';
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        document.getElementById('dynamic-alert').style.display = 'none';
    }

    function searchPurchaseOrder() {
        hideAlert();
        const orderNumber = document.getElementById('search-order-input').value.trim();
        if (!orderNumber) {
            showAlert('Por favor, informe o número do pedido de compra.');
            return;
        }

        document.getElementById('search-loading').style.display = 'block';
        document.getElementById('order-details').style.display = 'none';

        fetch(`/compras/search?order_number=${orderNumber}`)
            .then(res => {
                if (!res.ok) throw new Error('Erro na resposta do servidor.');
                return res.json();
            })
            .then(data => {
                document.getElementById('search-loading').style.display = 'none';
                if (data.error) {
                    showAlert(data.error);
                    return;
                }

                // Save items globally for client-side filtering
                currentOrderItems = data.items;

                // Fill form basic info
                document.getElementById('form-order-number').value = data.order_number;
                document.getElementById('order-supplier').innerText = data.supplier_code;
                document.getElementById('order-store').innerText = data.supplier_store;
                
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

                // Handle unique branches
                const uniqueBranches = [...new Set(data.items.map(item => item.branch))];
                const selectorContainer = document.getElementById('branch-selector-container');
                const branchSelect = document.getElementById('order-branch-select');

                if (uniqueBranches.length > 1) {
                    // Populate branches options
                    branchSelect.innerHTML = '<option value="todas">Todas as Filiais</option>';
                    uniqueBranches.forEach(b => {
                        branchSelect.innerHTML += `<option value="${b}">Filial ${b}</option>`;
                    });
                    selectorContainer.style.display = 'block';
                    
                    const selectValue = window.pendingSelectedBranch || 'todas';
                    branchSelect.value = selectValue;
                    filterBranchItems(selectValue);
                    window.pendingSelectedBranch = null;
                } else {
                    selectorContainer.style.display = 'none';
                    const singleBranch = uniqueBranches[0] || '01';
                    document.getElementById('form-branch').value = singleBranch;
                    document.getElementById('order-branch-text').innerText = singleBranch;
                    renderItemsTable(data.items);
                    window.pendingSelectedBranch = null;
                }

                document.getElementById('order-details').style.display = 'block';
            })
            .catch(err => {
                document.getElementById('search-loading').style.display = 'none';
                showAlert('Erro ao consultar o Protheus. Tente novamente.');
                console.error(err);
            });
    }

    function renderItemsTable(items) {
        const tbody = document.getElementById('order-items-body');
        tbody.innerHTML = '';
        items.forEach(item => {
            tbody.innerHTML += `
                <tr>
                    <td><strong>${item.product}</strong></td>
                    <td>${item.description}</td>
                    <td><span class="badge-status" style="background: rgba(56, 189, 248, 0.1); color: var(--accent-blue);">${item.branch}</span></td>
                    <td>${item.quantity}</td>
                    <td>R$ ${item.value.toFixed(2)}</td>
                </tr>
            `;
        });
    }

    function filterBranchItems(branch) {
        document.getElementById('form-branch').value = branch === 'todas' ? '' : branch;
        document.getElementById('order-branch-text').innerText = branch === 'todas' ? 'Todas' : branch;

        if (branch === 'todas') {
            renderItemsTable(currentOrderItems);
        } else {
            const filtered = currentOrderItems.filter(item => item.branch === branch);
            renderItemsTable(filtered);
        }
    }

    function selectRecentOrder(orderNumber, branch) {
        document.getElementById('search-order-input').value = orderNumber;
        window.pendingSelectedBranch = branch || null;
        searchPurchaseOrder();
    }

    function lookupCep(val) {
        const cep = val.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('collection_street').value = data.logradouro;
                        document.getElementById('collection_neighborhood').value = data.bairro;
                        document.getElementById('collection_city').value = data.localidade;
                        document.getElementById('collection_state').value = data.uf;
                        document.getElementById('collection_number').focus();
                        hideAlert();
                    } else {
                        showAlert('CEP não encontrado.');
                    }
                })
                .catch(err => console.error('Erro ao buscar CEP:', err));
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        
        const scheduleInput = document.getElementById('collection_schedule');
        if (scheduleInput) {
            scheduleInput.setAttribute('min', minDateTime);
        }
    });
</script>
@endsection
