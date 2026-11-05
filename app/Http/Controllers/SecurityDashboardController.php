<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class SecurityDashboardController extends Controller
{
    public function index()
    {
        $since = now()->subHours(24);
        $stats = [];

        foreach (['success', 'failed', 'blocked'] as $status) {
            $stats[$status] = LoginAttempt::where('status', $status)
                ->where('created_at', '>=', $since)
                ->count();
        }

        $suspiciousIps = LoginAttempt::select('ip_address', DB::raw('COUNT(*) as total'))
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('total')
            ->get();

        return view('security-dashboard', compact('stats', 'suspiciousIps'));
    }

    public function export()
    {
        $attempts = LoginAttempt::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->get();

        Log::channel('security')->notice('Exportación de evidencias', [
            'user_id' => auth()->id(),
            'records' => $attempts->count(),
        ]);

        $filename = 'evidencias-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($attempts) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'identifier', 'ip_address', 'status', 'user_agent', 'created_at']);
            foreach ($attempts as $a) {
                fputcsv($out, [$a->id, $a->identifier, $a->ip_address, $a->status, $a->user_agent, $a->created_at]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}