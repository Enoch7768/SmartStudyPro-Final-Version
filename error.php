<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (!defined('APP_START_TIME')) {
    define('APP_START_TIME', microtime(true));
}

$storageRoot = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
$logDirectory = $storageRoot . DIRECTORY_SEPARATOR . 'logs';
$errorLog = $logDirectory . DIRECTORY_SEPARATOR . 'errors.log';

if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0750, true);
}

function errorPageCode(int $status, string $source = 'APP'): string
{
    $timestamp = microtime(true);
    $random = bin2hex(random_bytes(4));

    return strtoupper(
        $source . '-' .
        $status . '-' .
        date('YmdHis') . '-' .
        substr(hash('sha256', $timestamp . $random . session_id()), 0, 8)
    );
}

function errorStatusMessage(int $status): array
{
    return match ($status) {
        400 => [
            'title' => 'Bad Request',
            'message' => 'The request could not be processed. Please check the information and try again.',
        ],
        401 => [
            'title' => 'Authentication Required',
            'message' => 'You need to sign in before you can access this resource.',
        ],
        403 => [
            'title' => 'Access Denied',
            'message' => 'You do not have permission to access this resource.',
        ],
        404 => [
            'title' => 'Page Not Found',
            'message' => 'The page you are looking for does not exist or may have been moved.',
        ],
        405 => [
            'title' => 'Method Not Allowed',
            'message' => 'This request method is not supported for this resource.',
        ],
        408 => [
            'title' => 'Request Timeout',
            'message' => 'The request took too long to complete. Please try again.',
        ],
        409 => [
            'title' => 'Conflict',
            'message' => 'The request could not be completed because it conflicts with existing data.',
        ],
        410 => [
            'title' => 'No Longer Available',
            'message' => 'This resource is no longer available.',
        ],
        413 => [
            'title' => 'Request Too Large',
            'message' => 'The information sent to the server is too large to process.',
        ],
        415 => [
            'title' => 'Unsupported Format',
            'message' => 'The submitted information uses a format that is not supported.',
        ],
        422 => [
            'title' => 'Unable to Process',
            'message' => 'The information provided could not be processed.',
        ],
        429 => [
            'title' => 'Too Many Requests',
            'message' => 'Too many requests were made in a short period. Please wait a moment and try again.',
        ],
        500 => [
            'title' => 'Something Went Wrong',
            'message' => 'An unexpected server error occurred. Our team can investigate it using the support code below.',
        ],
        501 => [
            'title' => 'Not Implemented',
            'message' => 'The requested operation is not currently supported.',
        ],
        502 => [
            'title' => 'Bad Gateway',
            'message' => 'The server received an invalid response from an upstream service.',
        ],
        503 => [
            'title' => 'Service Unavailable',
            'message' => 'The service is temporarily unavailable. Please try again shortly.',
        ],
        504 => [
            'title' => 'Gateway Timeout',
            'message' => 'An upstream service took too long to respond.',
        ],
        default => [
            'title' => 'Unexpected Error',
            'message' => 'Something unexpected happened. Our team can investigate it using the support code below.',
        ],
    };
}

function detectErrorSource(): string
{
    $source = strtoupper((string) ($_GET['source'] ?? ''));

    if ($source === 'CMS') {
        return 'CMS';
    }

    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');

    if (
        str_contains($referer, '/cms/') ||
        str_contains($referer, 'cms-init.php')
    ) {
        return 'CMS';
    }

    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

    foreach ($trace as $frame) {
        $file = (string) ($frame['file'] ?? '');

        if (
            str_contains(strtolower($file), 'cms-init.php') ||
            str_contains(
                strtolower($file),
                DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR
            )
        ) {
            return 'CMS';
        }
    }

    return 'APP';
}

function safeRequestPath(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $parsed = parse_url($uri);

    if (!is_array($parsed)) {
        return '/';
    }

    return mb_substr(
        (string) ($parsed['path'] ?? '/'),
        0,
        500
    );
}

function writeErrorLog(
    string $code,
    int $status,
    string $source,
    string $message,
    ?Throwable $exception = null
): void {
    global $errorLog;

    $entry = [
        'timestamp' => date('c'),
        'code' => $code,
        'status' => $status,
        'source' => $source,
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'uri' => safeRequestPath(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => mb_substr(
            (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'),
            0,
            500
        ),
        'message' => $message,
    ];

    if ($exception !== null) {
        $entry['exception'] = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
    }

    @file_put_contents(
        $errorLog,
        json_encode(
            $entry,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

$status = filter_var(
    $_GET['code']
        ?? $_SERVER['REDIRECT_STATUS']
        ?? $_SERVER['HTTP_STATUS_CODE']
        ?? 500,
    FILTER_VALIDATE_INT
);

if ($status === false || $status < 400 || $status > 599) {
    $status = 500;
}

$source = detectErrorSource();

$exception = null;

if (
    isset($GLOBALS['error_exception']) &&
    $GLOBALS['error_exception'] instanceof Throwable
) {
    $exception = $GLOBALS['error_exception'];
}

$details = errorStatusMessage($status);

$internalMessage = $exception?->getMessage() ?? $details['message'];

$code = errorPageCode($status, $source);

writeErrorLog(
    $code,
    $status,
    $source,
    $internalMessage,
    $exception
);

http_response_code($status);

if (
    isset($_SERVER['REQUEST_METHOD']) &&
    $_SERVER['REQUEST_METHOD'] === 'HEAD'
) {
    exit;
}

$homeUrl = function_exists('app_path') ? app_path('/') : '/';

$backUrl = !empty($_SERVER['HTTP_REFERER'])
    ? $_SERVER['HTTP_REFERER']
    : $homeUrl;

$backUrl = htmlspecialchars(
    $backUrl,
    ENT_QUOTES,
    'UTF-8'
);

$homeUrlEscaped = htmlspecialchars(
    $homeUrl,
    ENT_QUOTES,
    'UTF-8'
);

$sourceLabel = $source === 'CMS'
    ? 'CMS SYSTEM'
    : 'SMARTSTUDY PRO';

$statusDisplay = (string) $status;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        name="robots"
        content="noindex,nofollow,noarchive"
    >
    <meta
        name="theme-color"
        content="#07111f"
    >

    <title>
        <?= htmlspecialchars(
            $statusDisplay . ' · ' . $details['title'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <style>
        :root {
            color-scheme: dark;
            --bg: #050b14;
            --bg-secondary: #091525;
            --card: rgba(12, 24, 41, 0.72);
            --card-border: rgba(255, 255, 255, 0.09);
            --text: #f4f7fb;
            --muted: #91a0b5;
            --accent: #7dd3fc;
            --accent-secondary: #60a5fa;
            --shadow: 0 30px 100px rgba(0, 0, 0, 0.45);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
            overflow-x: hidden;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            color: var(--text);
            background:
                radial-gradient(
                    circle at 15% 20%,
                    rgba(96, 165, 250, 0.13),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 75%,
                    rgba(125, 211, 252, 0.10),
                    transparent 28%
                ),
                linear-gradient(
                    135deg,
                    var(--bg),
                    var(--bg-secondary)
                );
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(
                    rgba(255, 255, 255, 0.018) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.018) 1px,
                    transparent 1px
                );
            background-size: 44px 44px;
            mask-image: linear-gradient(
                to bottom,
                black,
                transparent
            );
        }

        .shell {
            width: min(760px, 100%);
            position: relative;
            z-index: 1;
        }

        .card {
            position: relative;
            padding: clamp(28px, 6vw, 58px);
            border: 1px solid var(--card-border);
            border-radius: 32px;
            background: var(--card);
            box-shadow: var(--shadow);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            top: -180px;
            right: -100px;
            border-radius: 50%;
            background: rgba(96, 165, 250, 0.13);
            filter: blur(30px);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 42px;
        }

        .brand-name {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .source {
            padding: 8px 12px;
            border: 1px solid rgba(125, 211, 252, 0.16);
            border-radius: 999px;
            background: rgba(125, 211, 252, 0.06);
            color: var(--accent);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.12em;
        }

        .error-code {
            margin: 0;
            font-size: clamp(76px, 15vw, 150px);
            line-height: 0.82;
            font-weight: 900;
            letter-spacing: -0.075em;
            background:
                linear-gradient(
                    110deg,
                    #ffffff 0%,
                    #a5d8ff 28%,
                    #60a5fa 48%,
                    #ffffff 68%,
                    #7dd3fc 100%
                );
            background-size: 220% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientMove 6s linear infinite;
        }

        @keyframes gradientMove {
            to {
                background-position: 220% center;
            }
        }

        .title {
            margin: 28px 0 12px;
            font-size: clamp(28px, 5vw, 44px);
            line-height: 1.05;
            letter-spacing: -0.045em;
        }

        .message {
            max-width: 600px;
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .support {
            margin-top: 32px;
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.16);
        }

        .support-label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .support-code {
            display: block;
            overflow-wrap: anywhere;
            color: #ffffff;
            font-family:
                "SFMono-Regular",
                Consolas,
                "Liberation Mono",
                monospace;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 20px;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 14px;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.055);
            text-decoration: none;
            font-size: 14px;
            font-weight: 750;
            transition:
                transform 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease;
        }

        .button:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .button.primary {
            border-color: transparent;
            background:
                linear-gradient(
                    135deg,
                    #60a5fa,
                    #38bdf8
                );
            color: #03101e;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            color: rgba(145, 160, 181, 0.65);
            font-size: 11px;
        }

        @media (max-width: 520px) {
            .card {
                border-radius: 24px;
            }

            .brand {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 32px;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
    <main class="shell">
        <section class="card">

            <div class="brand">
                <div class="brand-name">
                    SmartStudy Pro
                </div>

                <div class="source">
                    <?= htmlspecialchars(
                        $sourceLabel,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
            </div>

            <div class="error-code">
                <?= htmlspecialchars(
                    $statusDisplay,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <h1 class="title">
                <?= htmlspecialchars(
                    $details['title'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h1>

            <p class="message">
                <?= htmlspecialchars(
                    $details['message'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <div class="support">
                <span class="support-label">
                    Support code
                </span>

                <span class="support-code">
                    <?= htmlspecialchars(
                        $code,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>
            </div>

            <div class="actions">
                <a
                    class="button primary"
                    href="<?= $homeUrlEscaped ?>"
                >
                    Return Home
                </a>

                <a
                    class="button"
                    href="<?= $backUrl ?>"
                >
                    Go Back
                </a>
            </div>

        </section>

        <div class="footer">
            If you contact support, provide the support code above.
        </div>
    </main>
</body>
</html>

