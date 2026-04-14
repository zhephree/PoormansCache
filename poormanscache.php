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
	protected $path;
	protected $hashKeys = false;
	
	function __construct($path = './cache', $hashKeys = false){
		$this->path = $path;
		$this->hashKeys = $hashKeys;
	}
	
	function store($key, $value){
		$filename = $this->hashKeys? md5($key): basename($key);
		$what = serialize($value);
		$fullpath = $this->path . '/' . $filename;
		$temppath = $fullpath . uniqid('', true) . '.tmp';
		file_put_contents($temppath, $what);
		rename($temppath, $fullpath);
	}
	
	function get($key){
		$filename = $this->hashKeys? md5($key) : basename($key);
		$fullpath = $this->path . '/' . $filename;
		if(!file_exists($fullpath)) return null;

		$contents = file_get_contents($fullpath);
		if(!$contents) return false;

		return unserialize($contents);
	}
	
	function clear($key){
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
	
	function age($key){
		$filename = $this->hashKeys? md5($key) : basename($key);
		$fullpath = $this->path . '/' . $filename;
		if(!file_exists($fullpath)) return -1;

		$filedate = filemtime($fullpath);
		
		$now = time();
		$diff = $now - $filedate;
		$mins = ceil($diff / 60);
		return $mins;
	}
	
	function is_old($key,$maxAge){
		$age = $this->age($key);
		return ($age >= $maxAge || $age == -1);
	}
}

?>
