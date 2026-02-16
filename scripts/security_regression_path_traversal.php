<?php
require_once __DIR__ . '/../includes/validation.php';

$payloads = [
    '../products.php',
    '..\\products.php',
    '%2e%2e%2fproducts.php',
    '..%2fproducts.php',
    '..%255cproducts.php',
    '%252e%252e%252fproducts.php',
    "..\0/evil",
];

$shouldPass = [
    'Chicken Feed 50kg',
    "Supplier Co. Inc.",
    "10.50",
    "2026-02-17",
];

$failures = 0;

foreach ($payloads as $p) {
    if (!contains_path_traversal($p)) {
        fwrite(STDERR, "FAIL: expected traversal detected for: {$p}\n");
        $failures++;
    }
}

foreach ($shouldPass as $p) {
    if (contains_path_traversal($p)) {
        fwrite(STDERR, "FAIL: unexpected traversal detected for benign input: {$p}\n");
        $failures++;
    }
}

if ($failures > 0) {
    fwrite(STDERR, "\n{$failures} failure(s).\n");
    exit(1);
}

echo "OK: path traversal regression checks passed.\n";
