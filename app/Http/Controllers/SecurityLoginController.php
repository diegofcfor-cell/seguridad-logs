<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityLoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $key = 'security:block:' . hash('sha256', $ip);

        // 1. ¿IP bloqueada?
        if (Cache::has($key)) {
            $this->record($request, 'blocked');
            Log::channel('security')->warning('Intento desde IP bloqueada', ['ip' => $ip]);
            return response()->json(['message' => 'Acceso temporalmente restringido.'], 429);
        }

        // 2. ¿Credenciales correctas?
        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            $this->record($request, 'success');
            Log::channel('security')->info('Inicio de sesión exitoso', [
                'user_id' => Auth::id(),
                'ip' => $ip,
            ]);
            return response()->json(['message' => 'Autenticación correcta']);
        }

        // 3. Falló: registrar y contar
        $this->record($request, 'failed');

        // Mejora 1: detección por cuenta
        $email = mb_strtolower($data['email']);

        $accountFailures = LoginAttempt::where('identifier', $email)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($accountFailures >= 3) {
            Log::channel('security')->alert('Posible ataque dirigido a una cuenta', [
                'account_hash' => hash('sha256', $email),
                'failed_attempts' => $accountFailures,
                'window_minutes' => 15,
            ]);
        }

        $count = LoginAttempt::where('ip_address', $ip)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        Log::channel('security')->warning('Autenticación fallida', [
            'ip' => $ip,
            'failed_attempts' => $count,
        ]);

        // 4 y 5. Reglas de detección
        if ($count >= 5) {
            Cache::put($key, true, now()->addMinutes(10));
            Log::channel('security')->critical('Bloqueo temporal por intentos fallidos', [
                'ip' => $ip,
                'failed_attempts' => $count,
                'duration_minutes' => 10,
            ]);
        } elseif ($count >= 3) {
            Log::channel('security')->alert('Posible ataque de fuerza bruta', [
                'ip' => $ip,
                'failed_attempts' => $count,
            ]);
        }

        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    private function record(Request $request, string $status): void
    {
        LoginAttempt::create([
            'identifier' => mb_strtolower($request->input('email', '')),
            'ip_address' => $request->ip(),
            'status' => $status,
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'created_at' => now(),
        ]);
    }
}
