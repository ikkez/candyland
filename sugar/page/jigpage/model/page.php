<?php

namespace Sugar\Page\JigPage\Model;

use DB\SQL\Schema;

class Page extends \Sugar\Model\Base {

	protected $table='pages';

	protected $fieldConf = [
		'title' => [
			'type' => Schema::DT_VARCHAR256
		],
		'slug' => [
			'type' => Schema::DT_VARCHAR256
		],
		'alias' => [
			'type' => Schema::DT_VARCHAR256
		],
		'type' => [
			'type' => Schema::DT_VARCHAR256
		],
		'layout' => [
			'type' => Schema::DT_VARCHAR256
		],
		'template' => [
			'type' => Schema::DT_VARCHAR256
		],
		'controller' => [
			'type' => Schema::DT_VARCHAR256
		],
		'description' => [
			'type' => Schema::DT_VARCHAR256
		],
		'deleted_at' => [
			'type' => Schema::DT_TIMESTAMP,
			'default' => NULL
		],
	];

	function loadByAlias($name) {
		$this->load(['alias = ?',$name]);
	}

}