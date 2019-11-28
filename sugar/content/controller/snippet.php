<?php

namespace Sugar\Content\Controller;


class Snippet {

	protected $id;
	protected $flow;
	protected $scope;

	function __construct($id,$flow,$type) {
		$this->id = $id;
		$this->flow = $flow;
		$this->type = $type;
	}

	function create($conf) {
		$out = [
			'id'=>$this->id,
			'type'=>$this->type,
			'flow'=>$this->flow,
			'scope' => 'local',
			'settings' => [],
		];

		if (!empty($conf['settings']))
			foreach ($conf['settings'] as $field) {
				if (!empty($field['value']))
					$out['settings'][$field['name']]=$field['value'];
			}
		return $out;
	}

	function render() {

	}

}