@extends('layouts.app')

@section('title', 'Login - Tracking Logística')

@section('styles')
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 200px);
    }
    .login-card {
        max-width: 450px;
        width: 100%;
        padding: 2.5rem;
    }
    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .login-logo {
        font-size: 3rem;
        background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    .login-title {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.025em;
    }
    .login-subtitle {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        margin-bottom: 1.5rem;
    }
    .form-check-input {
        accent-color: var(--accent-blue);
        width: 16px;
        height: 16px;
    }
    .form-check-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        cursor: pointer;
    }
    .btn-submit {
        width: 100%;
        margin-top: 1rem;
    }
    .client-link {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.875rem;
    }
    .client-link a {
        color: var(--accent-blue);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    .client-link a:hover {
        color: #7dd3fc;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="glass-card login-card">
        <div class="login-header">
            <div class="login-logo" style="background: none; -webkit-text-fill-color: initial; display: flex; justify-content: center; margin-bottom: 1.5rem;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-height: 70px; object-fit: contain;">
            </div>
            <h1 class="login-title">Acesso ao Painel</h1>
            <p class="login-subtitle">Entre com suas credenciais de acesso</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p style="margin: 0;">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="usuario@empresa.com" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Lembrar-me neste dispositivo</label>
            </div>

            <button type="submit" class="btn-primary btn-submit">
                Entrar <i class="bi bi-arrow-right-short"></i>
            </button>
        </form>

        <div class="client-link">
            <p>É cliente? <a href="{{ route('client.index') }}">Rastreie seu pedido aqui</a></p>
        </div>
    </div>
</div>
@endsection
