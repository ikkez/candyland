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
		$this->model->load(['file = ? and storage = ? and deleted_at = ?',$file,$s_key,NULL]);

		if ($this->model->dry()) {
			$this->model->file = $file;
			$this->model->storage = $s_key;
			$this->model->uuid = \Base::instance()->hash($s_key.$file.uniqid('',true));
		}

		$this->model->copyfrom($data,[
			'title','desc','alt','author','origin','allowed'
		]);

		$this->model->save();
		unset($this->cache[$s_key.$file]);
		return $this->model->id;
	}

	/**
	 * return file ID
	 * @return mixed
	 */
	function getIdentifier() {
		return $this->model->id;
	}

	/**
	 * return file ID
	 * @return mixed
	 */
	function getUUID() {
		return $this->model->uuid;
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
			$this->model->load(['file = ? and storage = ? and deleted_at = ?',$file,$s_key,NULL],null,$ttl);
			$this->cache[$s_key.$file]=$this->model->valid() ? $this->model->cast(null,0) : [];
		}
		return $this->cache[$s_key.$file];
	}

	/**
	 * load file meta by plain id
	 * @param $fileId
	 * @param int $ttl
	 * @return mixed
	 */
	function loadById($fileId,$ttl=0) {
		$s_key = $this->fs->getStorageKey();
		if (!isset($this->cache[$s_key.$fileId])) {
			$this->model->load(['_id = ? and storage = ? and deleted_at = ?',$fileId,$s_key,NULL],null,$ttl);
			$this->cache[$s_key.$fileId]=$this->model->valid() ? $this->model->cast(null,0) : [];
		}
		return $this->cache[$s_key.$fileId];
	}

	/**
	 * load file meta by identifier
	 * @param $fileId
	 * @param int $ttl
	 * @return mixed
	 */
	function loadByUUID($uuid,$ttl=0) {
		$s_key = $this->fs->getStorageKey();
		if (!isset($this->cache[$s_key.$uuid])) {
			$this->model->load(['uuid = ? and storage = ? and deleted_at = ?',$uuid,$s_key,NULL],null,$ttl);
			$this->cache[$s_key.$uuid]=$this->model->valid() ? $this->model->cast(null,0) : [];
		}
		return $this->cache[$s_key.$uuid];
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