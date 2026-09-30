<?php

function app_config(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    static $fileConfig = null;

    if ($fileConfig === null) {
        $fileConfig = [];
        $envFile = __DIR__ . '/.env';

        if (is_file($envFile) && is_readable($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if ($value !== '') {
                    $first = $value[0];
                    $last = $value[strlen($value) - 1];

                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $value = substr($value, 1, -1);
                    }
                }

                $fileConfig[$name] = $value;
            }
        }
    }

    return $fileConfig[$key] ?? $default;
}

function app_secret(): string {
    static $secret = null;

    if ($secret !== null) {
        return $secret;
    }

    $configured = app_config('APP_SECRET');

    if ($configured !== null && strlen($configured) >= 32) {
        return $secret = $configured;
    }

    $secretFile = __DIR__ . '/database/.app_secret';
    $dir = dirname($secretFile);

    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    if (is_file($secretFile)) {
        $stored = trim((string) file_get_contents($secretFile));

        if (strlen($stored) >= 64) {
            return $secret = $stored;
        }
    }

    $generated = bin2hex(random_bytes(32));
    file_put_contents($secretFile, $generated, LOCK_EX);

    return $secret = $generated;
}

function admin_password_hash(): ?string {
    $hash = app_config('ADMIN_PASSWORD_HASH');

    return $hash !== null && password_get_info($hash)['algo'] !== 0 ? $hash : null;
}
