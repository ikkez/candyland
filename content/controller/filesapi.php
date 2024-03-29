<?php

namespace Sugar\Content\Controller;

use Sugar\View\JSON;
use Sugar\Component;
use Sugar\File\FileReference;

class FilesAPI extends Component {

	/** @var JSON */
	protected $view;

	/** @var FileReference */
	public $files;

	function beforeroute(\Base $f3,$args) {
		$this->view = new JSON();
	}

	function afterroute(\Base $f3,$args) {
		$this->view->dump();
	}

	/**
	 * list all images
	 */
	function collection(\Base $f3, $args) {
		$files = $this->files->filesystem()->listDir('/','/^((?!^\.).)*$/');
		$out = [];
		foreach ($files as $filename=>$item) {
			if ($item['type'] == 'file') {
				$out[] = [
					'name'=>$filename,
					'path'=>$this->files->getPublicPath($item['basename']),
					'size'=>round($item['size'] / 1024),
				];
			}
		}

		$this->view->set('status','success');
		$this->view->set('files',$out);
	}

	/**
	 * accept file uploads
	 * @param \Base $f3
	 * @param $params
	 */
	function upload(\Base $f3, $args) {
		$error = false;
		$files = \Web::instance()->receive(function($file,$fieldName) use (&$error) {
			if ($file['size'] > ($this->fw->get('upload_max_size') * 1024)) {
				$error = $this->fw->get('ll.error.validation.file_size',
					[basename($file['name']),$this->fw->get('upload_max_size').' kb']);
				return false;
			}
			return TRUE;
		},true,true);


		if ($error) {
			$this->fw->status(400);
			$this->view->set('error',$error);
		}
		else {
			$this->view->set('status','success');
			if ($files) {
				$files = array_keys($files);
				$filepath = $files[0];

				// move file to record dir
				$file = pathinfo($filepath);
				$new_path = $file['basename'];
				$this->files->load($new_path);
				$this->files->setContent($this->fw->read($filepath));
				// update reference path and save as new
				$this->files->file=$new_path;
				$this->files->title=$file['basename'];
				$this->files->save();
				@unlink($filepath);
				$filepath = $this->files->getPath();

				$this->view->set('file',[
					'name'=>basename($new_path),
					'path'=>$this->files->getPublicPath($file['basename']),
					'size'=>round(filesize($filepath) / 1024),
				]);
			}
		}
	}

	//	function insert() {
	//		$path = $this->fw->get('POST.url');
	//		$width = $this->fw->get('POST.width');
	//		$crop = $this->fw->get('POST.crop');
	//
	//		if (is_file($path)) {
	//			$file = pathinfo($path);
	//			$new_path = 'images/'.$file['basename'];
	//			$this->files->load($path);
	//			$this->files->move($new_path);
	//			$this->files->file=$new_path;
	//			$this->files->save();
	//
	//			$view = new JSON();
	//			$view->set('status','success');
	//			$img = new \Image($new_path,false,'./');
	//			$size = [$img->width(),$img->height()];
	//			$view->set('size',$size);
	//			$view->set('alt','');
	//			$view->set('url',$new_path);
	//			$view->dump();
	//			exit();
	//		} else {
	//			$this->fw->status(400);
	//			$view = new JSON();
	//			$view->set('error','file not existing');
	//			$view->dump();
	//			exit();
	//		}
	//	}

}