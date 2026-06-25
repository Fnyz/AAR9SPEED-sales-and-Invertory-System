<?php
function send_low_stock_alert(string $itemName, int $qty): void
{
    $to   = $_ENV['MAIL_TO']   ?? '';
    $from = $_ENV['MAIL_FROM'] ?? 'noreply@aar9speed.local';
    if ($to === '') return;

    $subject = "Low Stock Alert: $itemName";
    $body    = "Product \"$itemName\" is running low.\nCurrent quantity: $qty.\n\nPlease reorder soon.";
    $headers = "From: $from\r\nContent-Type: text/plain; charset=UTF-8";

    @mail($to, $subject, $body, $headers);
}
