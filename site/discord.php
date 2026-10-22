<?php
// Sends messages to a Discord channel through a webhook.
// Needs DISCORD_WEBHOOK_URL in .env (Channel → Edit Channel → Integrations → Webhooks).

function discord_configured(): bool
{
    return strpos($_ENV['DISCORD_WEBHOOK_URL'] ?? '', 'http') === 0;
}

/** Send a Markdown message. Returns true when Discord accepted it. */
function discord_send(string $markdown): bool
{
    if (!discord_configured()) {
        return false;
    }

    // Discord rejects messages over 2,000 characters.
    if (mb_strlen($markdown) > 2000) {
        $markdown = mb_substr($markdown, 0, 1990) . "\n…";
    }
    $payload = json_encode([
        'username'         => 'AAR9SPEED',
        'content'          => $markdown,
        // Never let a product name like "@everyone" ping the whole server.
        'allowed_mentions' => ['parse' => []],
    ]);

    $ch = curl_init($_ENV['DISCORD_WEBHOOK_URL']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // A webhook answers 204 No Content (or 200 when asked to wait for the message).
    if ($status !== 204 && $status !== 200) {
        error_log('Discord send failed (HTTP ' . $status . '): ' . (is_string($body) ? $body : curl_error($ch)));
        return false;
    }
    return true;
}
