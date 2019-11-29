<?php

namespace Sugar\File;

use FAL\FTP;
use FAL\LocalFS;

class FileSystem extends \Sugar\Component implements \FAL\FileSystem {

	/** @var string */
	protected $adapter;
	
	/** @var \FAL\FileSystem */
	protected $obj;

	protected $storage_key;

	function init() {
		if ($this->adapter) {
			switch ($this->adapter) {
				case 'local':
					$this->obj = new LocalFS($this->config['path']);
					$this->storage_key = 'local:'.$this->config['path'];
					break;
				case 'ftp':
					$this->obj = new FTP(
						$this->config['path'],
						$this->config['host'],
						$this->config['user'],
						isset($this->config['password'])?$this->config['password']:'',
						isset($this->config['port'])?$this->config['port']:21,
						isset($this->config['passive'])?(bool)$this->config['passive']:false,
						isset($this->config['mode'])?$this->config['mode']:FTP_BINARY);
					$this->storage_key = 'ftp:'.$this->config['host'].'/'.$this->config['path'];
					break;
			}
		}
	}

	/**
	 * return adapter object
	 * @return \FAL\FileSystem
	 */
	public function adapter() {
		return $this->obj;
	}

	/**
	 * return storage based identifier
	 * @return mixed
	 */
	public function getStorageKey() {
		return $this->storage_key;
	}

	/**
	 * determine if the file exists
	 * @param $file
	 * @return mixed
	 */
	public function exists($file) {
		return $this->obj->exists($file);
	}

	/**
	 * return file content
	 * @param $file
	 * @return mixed
	 */
	public function read($file) {
		return $this->obj->read($file);
	}

	/**
	 * write file content
	 * @param $file
	 * @param $content
	 * @return mixed
	 */
	public function write($file,$content) {
		return $this->obj->write($file,$content);
	}

	/**
	 * delete a file
	 * @param $file
	 * @return mixed
	 */
	public function delete($file) {
		return $this->obj->delete($file);
	}

	/**
	 * rename a file or directory
	 * @param $from
	 * @param $to
	 * @return mixed
	 */
	public function move($from,$to) {
		return $this->obj->move($from,$to);
	}

	/**
	 * get last modified date
	 * @param $file
	 * @return mixed
	 */
	public function modified($file) {
		return $this->obj->modified($file);
	}

	/**
	 * get filesize in bytes
	 * @param $file
	 * @return mixed
	 */
	public function size($file) {
		return $this->obj->size($file);
	}

	/**
	 * return whether the item is a directory
	 * @param $dir
	 * @return mixed
	 */
	public function isDir($dir) {
		return $this->obj->isDir($dir);
	}

	/**
	 * list content of given path, can be filtered by regex
	 * @param $dir
	 * @param $filter
	 * @return array
	 */
	public function listDir($dir=NULL,$filter=NULL) {
		return $this->obj->listDir($dir,$filter);
	}

	/**
	 * create new directory
	 * @param $dir
	 * @return mixed
	 */
	public function createDir($dir) {
		return $this->obj->createDir($dir);
	}

	/**
	 * remove a directory
	 * @param $dir
	 * @return mixed
	 */
	public function removeDir($dir) {
		return $this->obj->removeDir($dir);
	}

	/**
	 * return filesystem engine key
	 * @return string
	 */
	public function engine() {
		return $this->obj->engine();
	}
}