<?php
/**
 *  Poorman's Cache v1 - Decent PHP caching
 *
 *  @author Geoffrey Gauchet <geoff@zhephree.com>
 *  @link http://zhephree.com Website
 *  @link http://github.com/zhephree GitHub Repositories
 *  @license GPL, v2
 */
class PoormansCache {
	protected string $path;
	protected bool $hashKeys = false;
	
	function __construct(string $path = './cache', bool $hashKeys = false){
		$this->path = $path;
		$this->hashKeys = $hashKeys;
	}
	
	function store(string $key, mixed $value): bool {
		if(empty($key)) return false;

		$filename = $this->hashKeys? md5($key): basename($key);
		$what = serialize($value);
		$fullpath = $this->path . '/' . $filename;
		$temppath = $fullpath . uniqid('', true) . '.tmp';
		file_put_contents($temppath, $what);
		rename($temppath, $fullpath);
		return true;
	}
	
	function get(string $key): mixed {
		if(empty($key)) return null;

		$filename = $this->hashKeys? md5($key) : basename($key);
		$fullpath = $this->path . '/' . $filename;
		if(!file_exists($fullpath)) return null;

		$contents = file_get_contents($fullpath);
		if(!$contents) return false;

		return unserialize($contents);
	}
	
	function clear(string $key): bool {
		if(empty($key)) return false;

		if((strpos($key, '*') !== false || strpos($key, '?') !== false) && $this->hashKeys){
			throw new Exception('Wildcard (*,?) use in keys is not supported when hashKeys is true.');
		}

		if((strpos($key, '*') !== false || strpos($key, '?') !== false) && !$this->hashKeys){
			$returnValue = false;
			foreach(glob($this->path . '/' .$key) as $fullpath){
				if(file_exists($fullpath)){
					if(unlink($fullpath)){
						$returnValue =  true;
					}
				}		
			}

			return $returnValue;
		}else{
			$filename = $this->hashKeys? md5($key) : basename($key);
			$fullpath = $this->path . '/' . $filename;
			if(!file_exists($fullpath)) return false;


			if(unlink($fullpath)){
				return true;
			}

			return false;
		}

	}
	
	function age(string $key): int {
		if(empty($key)) return -1;

		$filename = $this->hashKeys? md5($key) : basename($key);
		$fullpath = $this->path . '/' . $filename;
		if(!file_exists($fullpath)) return -1;

		clearstatcache(true, $fullpath);
		$filedate = filemtime($fullpath);
		
		$now = time();
		$diff = $now - $filedate;
		$mins = ceil($diff / 60);
		return $mins;
	}
	
	function is_old(string $key, int $maxAge = 0): bool {
		if(empty($key)) return true;

		$age = $this->age($key);
		return ($age >= $maxAge || $age == -1);
	}
}

?>
