PoormansCache
=============

PHP caching that's quick and dirty, but it works. Great for shared servers with no memcached.

Requires PHP 8.0+.

Usage
=====
```php
require_once('poormanscache.php');
$cache = new PoormansCache();

$key = 'user2375settings';
if ($cache->is_old($key, 60)) {
    // do some database stuff to read the data
    $data = some_function();
    $cache->store($key, $data);
} else {
    $data = $cache->get($key);
}
```

Methods
=======

### `__construct(string $path = './cache', bool $hashKeys = false)`

`$path` — optional. Defaults to the `cache` folder relative to the script. **The directory will be created automatically** if it does not exist. A `RuntimeException` is thrown if the directory cannot be created or is not writable.

`$hashKeys` — optional. When `true`, keys are stored as MD5 hashes instead of plaintext. Plaintext keys are useful for grouping caches so they can be cleared together with wildcards.

---

### `store(string $key, mixed $value): bool`

`$key` — identifier for the cached data. Can be a SQL query, a descriptive name, etc.

`$value` — anything that can be `serialize()`'d: string, array, object, etc.

**returns:** `true` on success, `false` on failure.

---

### `get(string $key): mixed`

`$key` — the identifier used with `store()`.

**returns:** the cached value (any type), `null` if the cache entry does not exist or cannot be read.

---

### `clear(string $key): bool`

`$key` — the identifier used with `store()`.

When `$hashKeys` is `false`, wildcard patterns `*` and `?` are supported. e.g. `$cache->clear('user*')`. Wildcards are **not** supported when `$hashKeys` is `true` (throws an `Exception`).

**returns:** `true` if at least one file was removed, `false` otherwise.

---

### `age(string $key): int`

`$key` — the identifier used with `store()`.

**returns:** age in minutes of the cached entry, or `-1` if the entry does not exist.

---

### `is_old(string $key, int $maxAge = 0): bool`

`$key` — the identifier used with `store()`.

`$maxAge` — maximum acceptable age in minutes.

**returns:** `true` if the cached entry is older than `$maxAge` or does not exist, `false` otherwise.

---

Smoke test
==========

```bash
php tests/smoke.php
```
