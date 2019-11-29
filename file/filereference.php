<?php

namespace Sugar\File;

use Sugar\ComponentTrait;

class FileReference extends \FAL {

	use ComponentTrait;

	public function __construct(FileSystem $filesystem) {
		$meta = new FileMeta(new FileModel(),$filesystem);
		parent::__construct($filesystem,$meta);
	}

	public function filesystem() {
		return $this->fs;
	}
}