<?php
// One place to send automation messages to every configured channel
// (Telegram and/or Discord). Messages are written in Telegram's small HTML
// subset — <b>, <code> and escaped text — and converted for Discord.

require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/discord.php';

/** Why each channel failed during this request, e.g. ['Discord' => 'HTTP 403: …']. */
function notify_record_error(string $channel, string $detail): void
{
    // Keep it short and readable: strip HTML error pages down to text.
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($detail)));
    $GLOBALS['notify_errors'][$channel] = mb_substr($text, 0, 300);
}

function notify_errors(): array
{
    return $GLOBALS['notify_errors'] ?? [];
}

function notify_configured(): bool
{
    return telegram_configured() || discord_configured();
}

/** Names of the channels that are set up, e.g. ['Telegram', 'Discord']. */
function notify_channels(): array
{
    return array_values(array_filter([
        telegram_configured() ? 'Telegram' : null,
        discord_configured() ? 'Discord' : null,
    ]));
}

/** Escape text (product names, etc.) before putting it in a message. */
function notify_escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Turn the message HTML into Discord Markdown. */
function notify_html_to_markdown(string $html): string
{
    $md = preg_replace(['~</?b>~i', '~</?code>~i'], ['**', '`'], $html);
    $md = strip_tags($md);
    return html_entity_decode($md, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Send to every configured channel. True when at least one accepted it. */
function notify_send(string $html): bool
{
    $sent = false;
    if (telegram_configured()) {
        $sent = telegram_send($html) || $sent;
    }
    if (discord_configured()) {
        $sent = discord_send(notify_html_to_markdown($html)) || $sent;
    }
    return $sent;
}
