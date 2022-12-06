<?php

namespace Sugar\Content\Model;

use \DB\Jig\Mapper;

class Content extends Mapper {

    /**
     * @param string $file_name
     * @param \DB\Jig $jig_db
     * @param string|null $version
     */
	function __construct($file_name, \DB\Jig $jig_db, ?string $version=null) {
		parent::__construct($jig_db,$file_name.'.json');
		$this->load(array('isset(@version) && @version = ?',$version ?? 'master'));
	}

    public function loadLatestVersion() {
        $versions = $this->versions();
        if (!empty($versions)) {
            $latestVersion = $versions[0];
            $this->load(array('isset(@version) && @version = ?', $latestVersion));
        }
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
		$this->erase(['@version != ? && @version < ? && !isset(@label)', 'master',
			time() - (60 * 60 * 24 * 7)]); // 7 days backup for history (not draft)

		$this->erase(['@version != ? && @version < ?  && isset(@label)', 'master',
			time() - (60 * 60 * 24 * 30)]); // 30 days backup for drafts

		$all = $this->find(['@version != ? && !isset(@label)','master'],['order'=>'version SORT_DESC']);
		$max = 10;
		$c=count($all);
		if ($c > $max) {
			for ($i = $max;$i<=$c;$i++) {
				$all[$i-1]->erase();
			}
		}
	}

	function initNewVersion($versionName = 'master') {
		if ($this->valid()) {
			$this->copyto('model_backup');
		}
		$this->set('version', $versionName);
		$this->clear('label');
	}

	function commitBackup() {
		$f3 = \Base::instance();
		if ($f3->exists('model_backup')) {
			$mapper=clone($this);
			$mapper->reset();
			$mapper->copyfrom('model_backup');
			$mapper->save();
			$f3->clear('model_backup');
			$this->cleanUpBackups();
		}
	}
}
