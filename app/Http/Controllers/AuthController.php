<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return $this->redirectUser(Auth::user());
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas estão incorretas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    protected function redirectUser($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'estoque':
                return redirect()->route('estoque.dashboard');
            case 'compras':
                return redirect()->route('compras.dashboard');
            case 'logistica':
                return redirect()->route('logistica.dashboard');
            case 'motorista':
                return redirect()->route('motorista.dashboard');
            case 'diretoria':
                return redirect()->route('diretoria.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->with('error', 'Função de usuário desconhecida.');
        }
    }
}
