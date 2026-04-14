<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../poormanscache.php';

function fail(string $msg): void {
    fwrite(STDERR, "FAIL: $msg\n");
    exit(2);
}

function ok(string $msg): void {
    fwrite(STDOUT, "OK: $msg\n");
}

$cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pmcache_test_' . uniqid();

try {
    $c = new PoormansCache($cacheDir, false);
} catch (Throwable $e) {
    fail('Constructor failed: ' . $e->getMessage());
}

// store/get
if (!$c->store('key1', 'value1')) {
    fail('store(key1) returned false');
}
$val = $c->get('key1');
if ($val !== 'value1') {
    fail('get(key1) returned unexpected value: ' . var_export($val, true));
}
ok('store/get');

// age
$age = $c->age('key1');
if (!is_int($age) || $age < 0) {
    fail('age(key1) invalid: ' . var_export($age, true));
}
ok('age');

// is_old with maxAge=1 should be boolean
$isOld = $c->is_old('key1', 1);
if (!is_bool($isOld)) {
    fail('is_old returned non-bool');
}
ok('is_old');

// clear single
if (!$c->clear('key1')) {
    fail('clear(key1) failed');
}
if ($c->get('key1') !== null) {
    fail('get after clear did not return null');
}
ok('clear single');

// wildcard clear
if (!$c->store('a_one', 123) || !$c->store('a_two', 456)) {
    fail('setup for wildcard failed');
}
$beforeA = $c->get('a_one');
$beforeB = $c->get('a_two');
if ($beforeA !== 123 || $beforeB !== 456) {
    fail('wildcard setup values mismatch');
}
if (!$c->clear('a_*')) {
    fail('wildcard clear reported false');
}
if ($c->get('a_one') !== null || $c->get('a_two') !== null) {
    fail('wildcard clear did not remove files');
}
ok('wildcard clear');

// hashKeys + wildcard should throw
try {
    $ch = new PoormansCache($cacheDir, true);
    try {
        $ch->clear('b_*');
        fail('Expected exception for wildcard with hashKeys, but none thrown');
    } catch (Exception $e) {
        ok('wildcard with hashKeys throws');
    }
} catch (Throwable $e) {
    // If constructor fails because dir exists and not writable, ignore for this test
    fail('Constructor for hashKeys failed: ' . $e->getMessage());
}

// cleanup
$files = glob($cacheDir . DIRECTORY_SEPARATOR . '*') ?: [];
foreach ($files as $f) {
    @unlink($f);
}
@rmdir($cacheDir);

fwrite(STDOUT, "ALL OK\n");
exit(0);
