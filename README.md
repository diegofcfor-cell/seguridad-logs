# TP Logging y detección de incidentes de seguridad en Laravel

Trabajo práctico de Programación IV (Tecnicatura Universitaria en Programación, UTN).

Aplicación Laravel que registra los intentos de autenticación, detecta patrones sospechosos por IP y por cuenta, bloquea temporalmente una IP después de 5 fallos en 5 minutos y ofrece un panel de monitoreo protegido con exportación de evidencias.

**Informe completo, pruebas y capturas:** [docs/INFORME.md](docs/INFORME.md)

## Archivos principales

| Archivo | Qué hace |
|---|---|
| `config/logging.php` | Canal `security` (archivo diario, 30 días) |
| `database/migrations/..._create_login_attempts_table.php` | Tabla de auditoría `login_attempts` |
| `app/Models/LoginAttempt.php` | Modelo de la tabla |
| `app/Http/Controllers/SecurityLoginController.php` | Login, registro, detección y bloqueo |
| `app/Http/Controllers/SecurityDashboardController.php` | Panel de monitoreo y exportación CSV |
| `app/Providers/AppServiceProvider.php` | Gate `viewSecurityDashboard` |
| `routes/web.php` | Rutas `/security-login`, `/security-dashboard` y `/security-dashboard/export` |
| `resources/views/security-login.blade.php` | Formulario de prueba |
| `resources/views/security-dashboard.blade.php` | Vista del panel |

## Cómo ejecutarlo

```
composer install
cp .env.example .env
php artisan key:generate
# configurar la base de datos MySQL y APP_TIMEZONE en .env
php artisan migrate
php artisan serve
```

Luego abrir `http://127.0.0.1:8000/security-login`.
