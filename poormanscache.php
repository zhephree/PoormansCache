<?php
declare(strict_types=1);
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

	private function getKey(string $key): string {
		return $this->hashKeys ? md5($key) : str_replace(['/', '\\'], '_', $key);
	}

	public function __construct(string $path = './cache', bool $hashKeys = false) {
		$normalized = rtrim($path, DIRECTORY_SEPARATOR);
		if ($normalized === '') {
			$normalized = '.';
		}

		if (!is_dir($normalized)) {
			if (!mkdir($normalized, 0777, true) && !is_dir($normalized)) {
				throw new RuntimeException(sprintf('Unable to create cache directory: %s', $normalized));
			}
		}

		if (!is_writable($normalized)) {
			throw new RuntimeException(sprintf('Cache directory is not writable: %s', $normalized));
		}

		$this->path = $normalized;
		$this->hashKeys = $hashKeys;
	}

	public function store(string $key, mixed $value): bool {
		if ($key === '') {
			return false;
		}

		$filename = $this->getKey($key);
		$what = serialize($value);
		$fullpath = $this->path . DIRECTORY_SEPARATOR . $filename;
		$temppath = $fullpath . '.' . uniqid('', true) . '.tmp';

		$bytes = @file_put_contents($temppath, $what, LOCK_EX);
		if ($bytes === false) {
			return false;
		}

		if (!@rename($temppath, $fullpath)) {
			@unlink($temppath);
			return false;
		}

		return true;
	}

	public function get(string $key): mixed {
		if ($key === '') {
			return null;
		}

		$filename = $this->getKey($key);
		$fullpath = $this->path . DIRECTORY_SEPARATOR . $filename;
		if (!file_exists($fullpath)) {
			return null;
		}

		$contents = @file_get_contents($fullpath);
		if ($contents === false) {
			return null;
		}

		return @unserialize($contents, ['allowed_classes' => true]);
	}

	public function clear(string $key): bool {
		if ($key === '') {
			return false;
		}

		if ((strpbrk($key, '*?') !== false) && $this->hashKeys) {
			throw new Exception('Wildcard (*,?) use in keys is not supported when hashKeys is true.');
		}

		if (strpbrk($key, '*?') !== false && !$this->hashKeys) {
			$returnValue = false;
			$matches = glob($this->path . DIRECTORY_SEPARATOR . $key) ?: [];
			foreach ($matches as $fullpath) {
				if (file_exists($fullpath) && @unlink($fullpath)) {
					$returnValue = true;
				}
			}

			return $returnValue;
		}

		$filename = $this->getKey($key);
		$fullpath = $this->path . DIRECTORY_SEPARATOR . $filename;
		if (!file_exists($fullpath)) {
			return false;
		}

		return @unlink($fullpath);
	}

	public function age(string $key): int {
		if ($key === '') {
			return -1;
		}

		$filename = $this->getKey($key);
		$fullpath = $this->path . DIRECTORY_SEPARATOR . $filename;
		if (!file_exists($fullpath)) {
			return -1;
		}

		clearstatcache(true, $fullpath);
		$filedate = filemtime($fullpath);
		if ($filedate === false) {
			return -1;
		}

		$now = time();
		$diff = $now - $filedate;
		$mins = (int) ceil($diff / 60);
		return $mins;
	}

	public function is_old(string $key, int $maxAge = 0): bool {
		if ($key === '') {
			return true;
		}

		$age = $this->age($key);
		return ($age >= $maxAge || $age === -1);
	}
}

