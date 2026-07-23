<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Logistics Tracking System')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-blue: #38bdf8;
            --accent-green: #34d399;
            --accent-red: #f87171;
            --accent-purple: #c084fc;
            --glass-glow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(192, 132, 252, 0.08) 0px, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Glassmorphism Panel Layout */
        header {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.025em;
        }

        .brand i {
            color: var(--accent-blue);
            font-size: 1.5rem;
        }

        .brand span {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .btn-logout {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            color: var(--accent-red);
            border-color: rgba(248, 113, 113, 0.4);
            background: rgba(248, 113, 113, 0.05);
        }

        main {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* Common Premium Components */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--glass-glow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: -0.025em;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), #0284c7);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }

        /* Alert notifications */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .alert-danger {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.2);
            color: var(--accent-red);
        }

        .alert-success {
            background: rgba(52, 211, 153, 0.1);
            border: 1px solid rgba(52, 211, 153, 0.2);
            color: var(--accent-green);
        }

        /* Footer */
        footer {
            background: rgba(15, 23, 42, 0.8);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: auto;
        }

        /* Grid utilities */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            .user-nav {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    @auth
    <header>
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="brand" style="display: flex; align-items: center; gap: 0.5rem;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 38px; object-fit: contain;">
            <span>TRACKING LOGÍSTICA</span>
        </a>
        <div class="user-nav">
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-role">{{ auth()->user()->role }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
        </div>
    </header>
    @endauth

    <main>
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Tracking Logística - Integração Protheus</p>
    </footer>

    @yield('scripts')
</body>
</html>
