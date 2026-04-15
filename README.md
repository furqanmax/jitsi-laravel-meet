# Jitsi Laravel Meet Package

Laravel package for Jitsi Meet integration with real-time server-side timer control.

## Requirements

- PHP 8.1+
- Laravel 9.0, 10.0, 11.0, 12.0, or 13.0

## Installation

### 1. Require the Package

```bash
composer require furqan/jitsi-laravel-meet
```

The package will auto-discover and register the service provider.

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=jitsi-config
php artisan vendor:publish --tag=jitsi-views
```

This publishes:
- `config/jitsi.php` - Package configuration
- `resources/views/vendor/jitsi/embed.blade.php` - View template

### 3. Configure Environment

Add to your `.env` file:

```env
JITSI_URL=meet.eshare.ai
JITSI_REDIRECT_URL=/
```


### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `JITSI_URL` | `meet.eshare.ai` | Jitsi server URL |
| `JITSI_REDIRECT_URL` | `/` | Redirect URL after meeting ends |
| `JITSI_WARNING_YELLOW_SECONDS` | `300` | Yellow warning when this many seconds left |
| `JITSI_WARNING_RED_SECONDS` | `120` | Red warning when this many seconds left |


## Usage

### Basic Usage

In your Blade view:

```blade
@include('vendor.jitsi.embed', [
    'meetingCode' => $meetingCode,
    'userName'    => $userName,
    'startTime'   => $startTime,
    'endTime'     => $endTime,
])
```

## How It Works

1. **Server Authority**: All time calculations happen on the server
2. **Authorization**: By default, only the meeting owner can access
3. **Auto Hangup**: When time expires, user is automatically disconnected
