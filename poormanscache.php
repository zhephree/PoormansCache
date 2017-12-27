<?php
/**
 *  Poorman's Cache v1 - Decent PHP caching
 *
 *  @author Geoffrey Gauchet <geoff@zhephree.com>
 *  @link http://zhephree.com Website
 *  @link http://github.com/zhephree GitHub Repositories
 *  @license GPL, v2
 */
class PoormansCache

{
	protected $path;
	function __construct($path = './cache')
	{
		$this->path = $path;
	}

	function store($key, $value)
	{
		$filename = md5($key);
		$what = serialize($value);
		$fullpath = $this->path . '/' . $filename;
		$updateFile = false;
		$fh = fopen($fullpath, 'w');
		fwrite($fh, $what);
		fclose($fh);
	}

	function get($key)
	{
		$filename = md5($key);
		$fullpath = $this->path . '/' . $filename;
		if (file_exists($fullpath)) {
			$fh = fopen($fullpath, 'r');
			$contents = fread($fh, filesize($fullpath));
			fclose($fh);
			return unserialize($contents);
		}
		else {
			return false;
		}
	}

	function clear($key)
	{
		$filename = md5($key);
		$fullpath = $this->path . '/' . $filename;
		if (file_exists($fullpath)) {
			if (unlink($fullpath)) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	function age($key)
	{
		$filename = md5($key);
		$fullpath = $this->path . '/' . $filename;
		if (file_exists($fullpath)) {
			$filedate = filemtime($fullpath);
			$now = time();
			$diff = $now - $filedate;
			$mins = ceil($diff / 60);
			return $mins;
		}
		else {
			return -1;
		}
	}

	function is_old($key, $maxAge)
	{
		$age = $this->age($key);
		if ($age >= $maxAge || $age == - 1) {
			return true;
		}
		else {
			return false;
		}
	}
}

?>
