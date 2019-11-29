<?php

namespace Sugar\Log\Writer;

use Sugar\Component;

class File extends Component implements WriterInterface {

	/** @var \Log */
	protected $logger;

	protected $file_name='error.log';

	protected $file_rotation_format=FALSE;

	protected $sub_dir='';

	function init() {
		$bak=$this->fw->LOGS;
		$this->fw->LOGS = $this->fw->LOGS.$this->sub_dir;

		$fileName = $this->file_name;
		if ($this->file_rotation_format) {
			$exp = explode('.',$fileName);
			$ext = array_pop($exp);
			$base = implode('.',$exp);
			$base.='_'.date($this->file_rotation_format);
			$fileName = $base.'.'.$ext;
		}
		$this->logger = new \Log($fileName);
		$this->fw->LOGS = $bak;
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
	function write($level,$message,array $context=array()) {
		$msg = '['.$level.'] '.$message;
		if ($context)
			$msg.=' ['.@json_encode($context).']';
		$this->logger->write($msg);
	}

	/**
	 * installer
	 * @return array
	 */
	static function install() {
		/** @var \Base $f3 */
		$f3 = \Base::instance();
		$out = [];
		if (!file_exists($f3->LOGS)) {
			$out[] = 'creating default log dir: '.$f3->LOGS;
			@mkdir($f3->LOGS,0775,TRUE);
		}
		return $out;
	}
}