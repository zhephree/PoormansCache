PoormansCache
=============

PHP caching that's quick and dirty, but it works. Great for shared servers with no memcached

Usage
=====
```php
require_once('poormanscache.php');
$cache = new PoormansCache();

$key = 'user2375settings';
if($cache->is_old($key,60)){
  //do some database stuff to read the data
  $data = some_function();
  $cache->store($key,$data);
}else{
  $data = $cache->get($key);
}
```

Methods
=======
__construct($path = './cache', $hashkeys = false)
------------------
`$path` is an optional parameter. If missing, it the cacheing directory will be the `cache` folder relative to the location of the script. PMC will **NOT** create the directory for you, so you must create it yourself and make it writeable.

`$hashkeys` is optional and determines wheher the keys are stored as md5 hashes, or in plaintext. Plaintext is useful for grouping caches so they can be easily cleared later.


store($key, $value)
-----------------
`$key` is just some identifier for the cached data. You can use your full SQL query, or something explicit you come up with.

`$value` is any type of data you need to cache: string, array, object, anything that can be `serialize()`'d

get($key)
---------
`$key` is the identifier you used with `store()`

**returns:** the cached item, which could be any type of data

clear($key)
-----------
`$key` is the identifier you used with `store()`
If you are not hashing keys, they can be cleared using the `*` wildcard. e.g. `$cache->clear('user*')`

**returns:** `true` if the clearing was successful, `false` if not

age($key)
---------
`$key` is the identifier you used with `store()`

**returns:** the age (in minutes) of the cached data if the cache for `$key` exists, otherwise returns -1

is_old($key, $age)
-----------------
`$key` is the identifier you used with `store()`

`$age` is the age in minutes you'd like to compare against

**returns:** if the age of the cached data for `$key` is greater than `$age`, returns `true`, otherwise, returns `false`
