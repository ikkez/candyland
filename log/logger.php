<?php

namespace Sugar\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Sugar\Log\Writer\WriterInterface;

class Logger extends \Sugar\Component implements LoggerInterface {

	use LoggerTrait;

	/** @var WriterInterface */
	protected $logWriter;

	function __construct(WriterInterface $logWriter) {
		$this->logWriter = $logWriter;
	}

	function init() {
		$levels = [
			'emergency',
			'alert',
			'critical',
			'error',
			'warning',
			'notice',
			'info',
			'debug',
		];
		$map = function($args,$context,$ev) {
			if (empty($args))
				return $args;
			if (!is_array($args))
				$args=['msg'=>$args];
			$msg = $args['msg'];
			$attr = $args;
			unset($attr['msg']);
			if ($context)
				$attr['class']=get_class($context);
			$this->log($ev['key'],$msg,$attr);
			return $args;
		};
		// register global events
		foreach ($levels as $level) {
			$this->ev->on('log.'.$level,$map);
		}
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function log($level,$message,array $context=array()) {
		$this->logWriter->write($level,$message,$context);
	}
}