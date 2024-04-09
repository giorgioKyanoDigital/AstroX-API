<?php

class Authentication_Totp extends Authentication_Hotp {
	private $startTime;
	private $timeInterval;

	public function __construct($algo = 'sha1', $start = 0, $ti = 30) {
		parent::__construct($algo);
		$this->startTime = $start;
		$this->timeInterval = $ti;
	}

	public function GenerateToken($key, $time = null, $length = 6) {
		if ($this->algo === 'sha256') {
			$key = $key . substr($key, 0, 12);
		} elseif ($this->algo === 'sha512') {
			$key = $key . $key . $key . substr($key, 0, 4);
		}

		if (is_null($time)) {
			$time = (new \DateTime())->getTimestamp();
		}

		$now = $time - $this->startTime;
		$count = floor($now / $this->timeInterval);

		return parent::GenerateToken($key, $count, $length);
	}
}
