<?php

namespace Sugar\Page\JigPage\Model;

use Validation\Traits\CortexTrait;

class Page extends \Sugar\Model\Base {

	use CortexTrait;

	protected $table='pages';

	protected $fieldConf = [
		'title' => [
			'type' => self::DT_VARCHAR256,
			'validate' => 'required',
		],
		'slug' => [
			'type' => self::DT_VARCHAR256,
			'validate' => 'required',
		],
		'alias' => [
			'type' => self::DT_VARCHAR256,
			'validate' => 'required',
		],
		'type' => [
			'type' => self::DT_VARCHAR256,
			'validate' => 'required',
		],
		'layout' => [
			'type' => self::DT_VARCHAR256,
			'validate' => 'required',
		],
		'template' => [
			'type' => self::DT_VARCHAR256
		],
		'controller' => [
			'type' => self::DT_VARCHAR256
		],
		'description' => [
			'type' => self::DT_VARCHAR256
		],
		'deleted_at' => [
			'type' => self::DT_TIMESTAMP,
			'default' => NULL
		],
		'lang' => [
			'type' => self::DT_VARCHAR128,
			'default' => 'en',
		],
		'cid' => array(
			'type' => self::DT_INT,
		),
	];

	function loadByAlias($name) {
		$this->load(['alias = ?',$name]);
	}

}
