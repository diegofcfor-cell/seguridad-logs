<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de seguridad</title>
</head>
<body>
    <h1>Panel de seguridad (últimas 24 horas)</h1>

    <p>Exitosos: {{ $stats['success'] }}</p>
    <p>Fallidos: {{ $stats['failed'] }}</p>
    <p>Bloqueados: {{ $stats['blocked'] }}</p>

    <p><a href="/security-dashboard/export">Exportar evidencias (CSV)</a></p>

    <h2>IP sospechosas (3 o más fallos en 5 minutos)</h2>
    <table border="1">
        <tr>
            <th>IP</th>
            <th>Fallos</th>
            <th>Clasificación</th>
        </tr>
        @foreach ($suspiciousIps as $item)
            <tr>
                <td>{{ $item->ip_address }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ $item->total >= 5 ? 'CRÍTICO' : 'ALERTA' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
