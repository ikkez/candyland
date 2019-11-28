<?php

namespace Sugar\Processing\Queue;

use Sugar\Model\Base;

class JobModel extends Base {

	const
		STATUS_IDLE = 0,
		STATUS_ACTIVE = 1,
		STATUS_DONE = 2,
		STATUS_ERROR = 3;

	protected $table = 'queue';

	protected $fieldConf = [
		'label' => [
			'type' => self::DT_VARCHAR128,
		],
		'handler' => [
			'type' => self::DT_VARCHAR256,
		],
		'job' => [
			'type' => self::DT_TEXT,
		],
		'status' => [
			'type' => self::DT_VARCHAR128,
			'default' => self::STATUS_IDLE
		],
		'channel' => [
			'type' => self::DT_VARCHAR128,
		],
		'run' => [
			'type' => self::DT_INT4,
			'default' => 0
		],
		'retry' => [
			'type' => self::DT_INT4,
			'default' => 0
		],
		'performance_ms' => [
			'type' => self::DT_DECIMAL,
			'default' => 0
		],
		'memory_mb' => [
			'type' => self::DT_DECIMAL,
			'default' => 0
		],
		'exec_at' => [
			'type' => self::DT_TIMESTAMP,
		],
		'created_at' => [
			'type' => self::DT_TIMESTAMP,
			'default'=> self::DF_CURRENT_TIMESTAMP,
		],
		'deleted_at' => [
			'type' => self::DT_TIMESTAMP,
			'default'=> NULL,
		],
	];

	function enableFilter() {
		return ['deleted_at = ?',NULL];
	}

}