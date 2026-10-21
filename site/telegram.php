<?php
// Sends messages to the owner's Telegram chat.
// Needs TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in .env (see README → Automation).

function telegram_configured(): bool
{
    return ($_ENV['TELEGRAM_BOT_TOKEN'] ?? '') !== '' && ($_ENV['TELEGRAM_CHAT_ID'] ?? '') !== '';
}

function telegram_escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Send an HTML-formatted message. Returns true when Telegram accepted it.
 * Telegram's HTML mode supports <b>, <i>, <code> and <a href>; escape everything else.
 */
function telegram_send(string $html): bool
{
    if (!telegram_configured()) {
        return false;
    }

    $url = 'https://api.telegram.org/bot' . $_ENV['TELEGRAM_BOT_TOKEN'] . '/sendMessage';
    $payload = http_build_query([
        'chat_id'                  => $_ENV['TELEGRAM_CHAT_ID'],
        'text'                     => $html,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => 'true',
    ]);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    } else {
        $context = stream_context_create(['http' => [
            'method'        => 'POST',
            'header'        => 'Content-Type: application/x-www-form-urlencoded',
            'content'       => $payload,
            'timeout'       => 10,
            'ignore_errors' => true,
        ]]);
        $body    = @file_get_contents($url, false, $context);
        $headers = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?? [])
            : ($http_response_header ?? []);
        $status  = isset($headers[0]) && preg_match('/\s(\d{3})\b/', $headers[0], $m) ? (int)$m[1] : 0;
    }

    if ($status !== 200) {
        error_log('Telegram send failed (HTTP ' . $status . '): ' . (is_string($body) ? $body : 'no response'));
        return false;
    }
    return true;
}
