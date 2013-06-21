PoormansCache
=============

PHP caching that's quick and dirty, but it works. Great for shared servers with no memcached

Usage
=====
```php
require_once('poormanscache.php');
$cache=new PoormansCache();

$key='user2375settings';
if($cache->is_old($key,60)){
  //do some database stuff to read the data
  $data=some_function();
  $cache->store($key,$data);
}else{
  $data=$cache->get($key);
}
```

Methods
=======
store($key,$data)
-----------------
`$key` is just some identifier for the cached data. You can use your full SQL query, or something explicit you come up with.
`$data` is any type of data you need to cache: string, array, object, anything that can be serialize()'d

get($key)
---------
`$key` is the identifier you used with `store()`
**returns:** the cached item, which could be any type of data

clear($key)
-----------
`$key` is the identifier you used with `store()`
**returns:** `true` if the clearing was successful, `false` if not

age($key)
---------
`$key` is the identifier you used with `store()`
**returns:** the age (in minutes) of the cached data if the cache for `$key` exists, otherwise returns -1

is_old($key,$age)
-----------------
`$key` is the identifier you used with `store()`
`$age` is the age in minutes you'd like to compare against
**returns:** if the age of the cached data for `$key` is greater than `$age`, returns `true`, otherwise, returns `false`
