<?php

namespace Sugar\File;

use Sugar\ComponentTrait;

class FileReference extends \FAL {

	use ComponentTrait;

	public function __construct(FileSystem $filesystem) {
		$meta = new FileMeta(new FileModel(),$filesystem);
		parent::__construct($filesystem,$meta);
	}

	/**
	 * load file by reference id
	 * @param $uuid
	 * @param int $ttl
	 * @return bool
	 */
	function loadByUUID($uuid,$ttl=0) {
		$fileData = $this->metaHandle->loadByUUID($uuid,$ttl);
		if ($fileData)
			return $this->load($fileData['file'],$ttl);
		return false;
	}

	public function getPath() {
		$exp = explode(':',$this->fs->getStorageKey(),2);
		return $exp[1].$this->get('file');
	}

	public function filesystem() {
		return $this->fs;
	}
}