<?php
// One-time migration: hash any staff password still stored as plain text.
// Login no longer accepts plain-text passwords, so run this once per database
// before deploying the updated login.php:
//
//   php migrations/004_hash_passwords.php
//
// Safe to run again: accounts that are already hashed are skipped.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/Database/Database.php';

$conn = (new Database())->getConnect();
if (!$conn instanceof PDO) {
    fwrite(STDERR, "Database connection failed. Check .env.\n");
    exit(1);
}
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$width = (int)$conn->query(
    "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'staff' AND COLUMN_NAME = 'staff_pass'"
)->fetchColumn();
if ($width < 60) {
    fwrite(STDERR, "staff.staff_pass is VARCHAR($width), too short for hashes. Run migrations/002_improvements.sql first.\n");
    exit(1);
}

$rows = $conn->query("SELECT STAFF_ID, STAFF_USER, staff_pass FROM staff")->fetchAll(PDO::FETCH_ASSOC);
$update = $conn->prepare("UPDATE staff SET staff_pass = :hash WHERE STAFF_ID = :id");
$converted = 0;

foreach ($rows as $row) {
    $stored = (string)$row['staff_pass'];
    if (password_get_info($stored)['algo']) {
        continue;
    }
    $update->execute([':hash' => password_hash($stored, PASSWORD_DEFAULT), ':id' => $row['STAFF_ID']]);
    echo "Hashed password for {$row['STAFF_USER']}\n";
    $converted++;
}

echo $converted === 0 ? "All passwords were already hashed.\n" : "Done: $converted account(s) converted.\n";
