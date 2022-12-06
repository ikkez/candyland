<?php

namespace Sugar\Page\JigPage\Model;

use Validation\Traits\CortexTrait;

/**
 * @property string title
 * @property string slug
 * @property string alias
 * @property string type
 * @property string layout
 * @property string template
 * @property string controller
 * @property string description
 * @property string deleted_at
 * @property string lang
 * @property string cid
 * @property bool enable
 */
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
			'validate' => 'required|unique',
		],
		'alias' => [
			'type' => self::DT_VARCHAR256,
			'validate' => 'required|unique',
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
        'enable' => array(
            'type' => self::DT_BOOL,
            'default' => false,
        ),
	];

	function loadByAlias($name) {
		$this->load(['alias = ?',$name]);
	}

}
