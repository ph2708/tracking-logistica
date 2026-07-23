@extends('layouts.app')

@section('title', 'Admin Dashboard - Users')

@section('styles')
<style>
    .admin-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
    }
    .user-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .user-list-table th, .user-list-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .user-list-table th {
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .user-list-table tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-estoque { background: rgba(56, 189, 248, 0.15); color: var(--accent-blue); }
    .badge-compras { background: rgba(192, 132, 252, 0.15); color: var(--accent-purple); }
    .badge-logistica { background: rgba(52, 211, 153, 0.15); color: var(--accent-green); }
    .badge-motorista { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
    .badge-diretoria { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
    .badge-admin { background: rgba(248, 113, 113, 0.15); color: var(--accent-red); }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    .btn-edit {
        background: transparent;
        border: 1px solid rgba(56, 189, 248, 0.3);
        color: var(--accent-blue);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-edit:hover {
        background: rgba(56, 189, 248, 0.1);
    }
    .btn-delete {
        background: transparent;
        border: 1px solid rgba(248, 113, 113, 0.3);
        color: var(--accent-red);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-delete:hover {
        background: rgba(248, 113, 113, 0.1);
    }

    @media (max-width: 992px) {
        .admin-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<h1 class="page-title"><i class="bi bi-shield-lock-fill"></i> Painel do Administrador</h1>

<div class="admin-grid">
    <!-- User Form -->
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;" id="form-title">Cadastrar Novo Usuário</h2>
        
        <form action="{{ route('admin.users.store') }}" method="POST" id="user-form">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            
            <div class="form-group">
                <label for="name" class="form-label">Nome Completo</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Ex: João da Silva" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="joao@empresa.com" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label" id="password-label">Senha</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 8 caracteres" required>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Função / Perfil</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="estoque">Estoque</option>
                    <option value="compras">Compras / Comprador</option>
                    <option value="logistica">Logística</option>
                    <option value="motorista">Motorista / Técnico</option>
                    <option value="diretoria">Diretoria</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1;" id="btn-submit-text">
                    <i class="bi bi-person-plus-fill"></i> Salvar
                </button>
                <button type="button" class="btn-secondary" id="btn-cancel" style="display: none;" onclick="resetForm()">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <div class="glass-card">
        <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Usuários Cadastrados</h2>
        
        <div style="overflow-x: auto;">
            <table class="user-list-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $user->role }}">{{ $user->role }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick="editUser({{ json_encode($user) }})" title="Editar Usuário">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este usuário?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete" title="Excluir Usuário">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function editUser(user) {
        document.getElementById('form-title').innerText = 'Editar Usuário';
        document.getElementById('user-form').action = '/admin/users/' + user.id;
        document.getElementById('form-method').value = 'PUT';
        
        document.getElementById('name').value = user.name;
        document.getElementById('email').value = user.email;
        document.getElementById('role').value = user.role;
        
        // Password is optional during edit
        document.getElementById('password').required = false;
        document.getElementById('password').placeholder = 'Deixe em branco para não alterar';
        document.getElementById('password-label').innerText = 'Senha (Opcional)';
        
        document.getElementById('btn-submit-text').innerHTML = '<i class="bi bi-save-fill"></i> Atualizar';
        document.getElementById('btn-cancel').style.display = 'block';
    }

    function resetForm() {
        document.getElementById('form-title').innerText = 'Cadastrar Novo Usuário';
        document.getElementById('user-form').action = '{{ route("admin.users.store") }}';
        document.getElementById('form-method').value = 'POST';
        
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('role').value = 'estoque';
        
        document.getElementById('password').required = true;
        document.getElementById('password').placeholder = 'Mínimo 8 caracteres';
        document.getElementById('password-label').innerText = 'Senha';
        
        document.getElementById('btn-submit-text').innerHTML = '<i class="bi bi-person-plus-fill"></i> Salvar';
        document.getElementById('btn-cancel').style.display = 'none';
    }
</script>
@endsection
