<?php

namespace Sugar\Content\Model;

use \DB\Jig\Mapper;

class Content extends Mapper {

	/**
	 * @param string $file_name
	 * @param \DB\Jig $jig_db
	 * @param string $version
	 */
	function __construct($file_name, \DB\Jig $jig_db, $version='master') {
		parent::__construct($jig_db,$file_name.'.json');
		$this->load(array('isset(@version) && @version = ?',$version));
	}

	/**
	 * @return array
	 */
	function versions() {
		$all = $this->find(null,array('order'=>'version SORT_DESC'));
		$versions = array();
		if ($all)
			$versions = \Matrix::instance()->pick($all,'version');
		return $versions;
	}

	function cleanUpBackups() {
		$this->erase(['@version != ? && @version < ?', 'master',
			time() - (60 * 60 * 24 * 14)]); // 14 days backup
		$all = $this->find(['@version != ?','master'],['order'=>'version SORT_DESC']);
		$max = 6;
		$c=count($all);
		if ($c > $max) {
			for ($i = $max;$i<=$c;$i++) {
				$all[$i-1]->erase();
			}
		}
	}

	function initBackup() {
		if ($this->valid()) {
			$this->copyto('model_backup');
		}
		$this->set('version','master');
	}

	function commitBackup() {
		/** @var \Base $f3 */
		$f3 = \Base::instance();

		if ($f3->exists('model_backup')) {
			$mapper=clone($this);
			$mapper->reset();
			$mapper->copyfrom('model_backup');
			$mapper->set('version',time());
			$mapper->save();
			$f3->clear('model_backup');
			$this->cleanUpBackups();
		}
	}
}