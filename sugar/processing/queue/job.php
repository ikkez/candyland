<?php

namespace Sugar\Processing\Queue;

use Sugar\Service\Factory;
use Sugar\Service\Registry;

class Job implements JobInterface {

	public
		$handler,
		$data;

	function __construct($handler=NULL,$args=NULL) {
		if ($handler) {
			$this->handler = $handler;
			$this->data = $args;
		}
	}

	function exec() {
		if (!$this->handler)
			return FALSE;
		return Factory::instance()->call($this->handler,[$this->data]);
	}

}