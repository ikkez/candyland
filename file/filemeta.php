<?php

namespace Sugar\File;

class FileMeta implements \FAL\MetaStorageInterface {

	/** @var FileModel */
	protected $model;

	/** @var FileSystem */
	protected $fs;

	/**
	 * fluent cache
	 * @var array
	 */
	protected $cache=[];

	function __construct(FileModel $model, FileSystem $fs) {
		$this->model = $model;
		$this->fs = $fs;
	}

	/**
	 * save file meta data
	 * @param $file
	 * @param $data
	 * @param int $ttl
	 */
	function save($file,$data,$ttl=0) {
		$s_key = $this->fs->getStorageKey();
		$this->model->load(['file = ? and storage = ?',$file,$s_key]);

		if ($this->model->dry()) {
			$this->model->file = $file;
			$this->model->storage = $s_key;
		}

		$this->model->copyfrom($data,[
			'title','desc','alt','author','origin','allowed'
		]);

		$this->model->save();
		unset($this->cache[$s_key.$file]);
	}

	/**
	 * load file meta
	 * @param $file
	 * @param int $ttl
	 * @return array
	 */
	function load($file,$ttl=0) {
		$s_key = $this->fs->getStorageKey();
		if (!isset($this->cache[$s_key.$file])) {
			$this->model->load(['file = ? and storage = ?',$file,$s_key],null,$ttl);
			$this->cache[$s_key.$file]=$this->model->valid() ? $this->model->cast(null,0) : [];
		}
		return $this->cache[$s_key.$file];
	}

	/**
	 * delete file meta
	 * @param $file
	 */
	function delete($file) {
		$s_key = $this->fs->getStorageKey();
		$this->model->load(['file = ? and storage = ?',$file,$s_key]);
		if ($this->model->valid())
			$this->model->erase();
	}
}