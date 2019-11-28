<?php

namespace Sugar\File;

class FileModel extends \Sugar\Model\Base {

	protected $table = 'files';

	protected $fieldConf = [
		'file' => [
			'type' => self::DT_VARCHAR256,
		],
		'title' => [
			'type' => self::DT_VARCHAR256,
		],
		'desc' => [
			'type' => self::DT_TEXT,
		],
		'alt' => [
			'type' => self::DT_VARCHAR256,
		],
		'author' => [
			'type' => self::DT_VARCHAR128,
		],
		'origin' => [
			'type' => self::DT_VARCHAR128,
		],
		'allowed' => [
			'type' => self::DT_BOOL,
		],
		'storage' => [
			'type' => self::DT_VARCHAR256,
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