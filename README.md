# Jitsi Laravel Meet Package

Laravel package for Jitsi Meet integration with real-time server-side timer control.

## Requirements

- PHP 8.1+
- Laravel 9.0, 10.0, 11.0, 12.0, or 13.0

## Installation

### 1. Add Repository to composer.json

Add the local repository path to your Laravel project's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "/path/to/vc/packages/jitsi-laravel-meet"
    }
]
```

### 2. Require the Package

```bash
composer require furqanamx/jitsi-laravel-meet
```

The package will auto-discover and register the service provider.

### 3. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=jitsi-config
php artisan vendor:publish --tag=jitsi-views
```

This publishes:
- `config/jitsi.php` - Package configuration
- `resources/views/vendor/jitsi/embed.blade.php` - View template

### 4. Configure Environment

Add to your `.env` file:

```env
JITSI_URL=meet.eshare.ai
JITSI_REDIRECT_URL=/
```

## Laravel 11+ Bootstrap Configuration

For Laravel 11+, add the service provider to `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    Furqanamx\JitsiLaravelMeet\JitsiLaravelMeetServiceProvider::class,
];
```

For Laravel 11+, you can also register routes in `bootstrap/app.php`:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Furqanamx\JitsiLaravelMeet\Http\Controllers\JitsiController;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withRouteExtensions(function ($router) {
        $router->get('/meeting/{code}/time-remaining', [JitsiController::class, 'timeRemaining'])
            ->name('jitsi.time-remaining');
    })
    ->create();
```

Or load routes from the package:

```php
->withRouting(
    // ...
    api: __DIR__.'/../routes/api.php',
)
```

Then add to your `routes/api.php`:

```php
use Illuminate\Support\Facades\Route;
use Furqanamx\JitsiLaravelMeet\Http\Controllers\JitsiController;

Route::get('/meeting/{code}/time-remaining', [JitsiController::class, 'timeRemaining'])
    ->name('jitsi.time-remaining');
```

## Configuration

Edit `config/jitsi.php` to customize:

```php
return [
    'url' => env('JITSI_URL', 'meet.eshare.ai'),
    'redirect_url' => env('JITSI_REDIRECT_URL', '/'),
    'timer_sync_interval' => env('JITSI_TIMER_SYNC_INTERVAL', 1000),
    'warning_thresholds' => [
        'yellow' => env('JITSI_WARNING_YELLOW_SECONDS', 300),
        'red' => env('JITSI_WARNING_RED_SECONDS', 120),
    ],
    'meeting_model' => null, // Set to your Meeting model class
    'authorize' => true,
    'check_user_meeting' => true,
];
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `JITSI_URL` | `meet.eshare.ai` | Jitsi server URL |
| `JITSI_REDIRECT_URL` | `/` | Redirect URL after meeting ends |
| `JITSI_TIMER_SYNC_INTERVAL` | `1000` | Timer sync interval in milliseconds |
| `JITSI_WARNING_YELLOW_SECONDS` | `300` | Yellow warning when this many seconds left |
| `JITSI_WARNING_RED_SECONDS` | `120` | Red warning when this many seconds left |

## Database Requirements

Your `Meeting` model must have these fields:

| Field | Type | Description |
|-------|------|-------------|
| `code` | string | Unique meeting code |
| `start_time` | datetime | Meeting start time |
| `end_time` | datetime | Meeting end time |
| `user_id` | integer | Owner user ID |

Example migration:

```php
Schema::create('meetings', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->dateTime('start_time');
    $table->dateTime('end_time');
    $table->timestamps();
});
```

## Usage

### Basic Usage

In your Blade view:

```blade
<x-jitsi::embed :meeting-code="$meeting->code" />
```

### With Custom Parameters

```blade
<x-jitsi::embed 
    :meeting-code="$meeting->code"
    :user-name="$user->name"
    :jitsi-url="'meet.example.com'"
    :redirect-url="'/dashboard'"
/>
```

### Component Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `meetingCode` | string | Yes | The meeting code |
| `userName` | string | No | Display name (defaults to Auth user name) |
| `jitsiUrl` | string | No | Jitsi server URL (defaults to config) |
| `redirectUrl` | string | No | Redirect URL after meeting (defaults to config) |

## How It Works

1. **Timer Sync**: The frontend syncs with the server every second via `/meeting/{code}/time-remaining`
2. **Server Authority**: All time calculations happen on the server
3. **Authorization**: By default, only the meeting owner can access
4. **Auto Hangup**: When time expires, user is automatically disconnected

## Customizing Meeting Model

Set your Meeting model in `config/jitsi.php`:

```php
'meeting_model' => App\Models\MyMeeting::class,
```

Or use a custom method for finding by code:

```php
public static function findByCode(string $code)
{
    return self::where('slug', $code)->first();
}
```

## API Endpoint

The package auto-registers this route:

```
GET /meeting/{code}/time-remaining
```

**Response:**

```json
{
    "remaining_seconds": 1800,
    "before_start_seconds": 0
}
```

If meeting hasn't started yet:
```json
{
    "remaining_seconds": 0,
    "before_start_seconds": 300
}
```

## Troubleshooting

### Route Not Found

For Laravel 11+, manually add to `bootstrap/providers.php`:

```php
Furqanamx\JitsiLaravelMeet\JitsiLaravelMeetServiceProvider::class,
```

Or for older versions, add to `config/app.php`:

```php
'providers' => [
    // ...
    Furqanamx\JitsiLaravelMeet\JitsiLaravelMeetServiceProvider::class,
],
```

### Timer Not Working

Check that your `Meeting` model has `start_time` and `end_time` columns properly set as datetime.

### Authorization Errors

Set `'authorize' => false` in config to disable authorization checks during development.
