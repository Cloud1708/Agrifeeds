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

// Additional regression: uploaded image validation should not allow path traversal
// or file-type spoofing.

function tmpfile_write($bytes) {
    $tmp = tempnam(sys_get_temp_dir(), 'agrifeeds_');
    file_put_contents($tmp, $bytes);
    return $tmp;
}

// 1) Traversal in original name should be rejected (even if content would be an image)
// Use a minimal PNG header so a naive validator might accept.
$fakePng = "\x89PNG\r\n\x1a\n" . str_repeat("\x00", 16);
$tmp1 = tmpfile_write($fakePng);
$res1 = validate_uploaded_image([
    'name' => '../products.php',
    'type' => 'image/png',
    'tmp_name' => $tmp1,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($tmp1),
]);
@unlink($tmp1);
if (!empty($res1['ok'])) {
    fwrite(STDERR, "FAIL: validate_uploaded_image unexpectedly accepted traversal filename.\n");
    exit(1);
}

// 2) Non-image content with an allowed extension should be rejected
$tmp2 = tmpfile_write("this is not an image");
$res2 = validate_uploaded_image([
    'name' => 'x.png',
    'type' => 'image/png',
    'tmp_name' => $tmp2,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($tmp2),
]);
@unlink($tmp2);
if (!empty($res2['ok'])) {
    fwrite(STDERR, "FAIL: validate_uploaded_image unexpectedly accepted non-image content.\n");
    exit(1);
}

echo "OK: upload validation regression checks passed.\n";
