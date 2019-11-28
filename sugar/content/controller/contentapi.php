<?php

namespace Sugar\Content\Controller;

use Sugar\Content\Service\ContentService;
use Sugar\Page\JigPage\Page;
use Sugar\Storage;
use Sugar\UI\Editor\ContentTools_v1_6_10\Provider;
use Sugar\View\JSON;
use Sugar\Component;
use Sugar\View\TemplateInterface;
use Sugar\View\ViewInterface;
use Sugar\Auth\SimpleAuth;
use Model\BackendUser;
use Sugar\File\FileReference;

class ContentAPI extends Component {

	/** @var \Sugar\View\TemplateInterface */
	protected $tmpl_renderer;

	/** @var JSON */
	protected $view;

	/** @var \Sugar\Content\Model\Content */
	protected $model;

	protected $db;

	/** @var SimpleAuth $auth */
	protected $auth;

	/** @var FileReference */
	public $files;

	/** @var \Sugar\Page\JigPage\Page */
	protected $page;

	/** @var BackendUser */
	protected $user;

	/** @var Provider */
	protected $editor_provider;

	function __construct(TemplateInterface $tmpl_renderer) {
		$this->tmpl_renderer = $tmpl_renderer;
	}

	function beforeroute(\Base $f3,$params) {
		$this->view = new JSON();
		if (!$this->editor_provider->isEnabled()) {
			$this->fw->error(403);
		}
	}

	function afterroute(\Base $f3,$params) {
		$this->view->dump();
	}

	/**
	 * save page contents
	 * @param \Base $f3
	 * @param $args
	 * @return bool
	 */
	function handle(\Base $f3, $args) {
		if (isset($args['action'])) {
			// load content model for current page
			if (isset($args['pid']))
				$this->model = $this->load($args['pid']);
			else
				$f3->error(400,'pid parameter required');

			$method = $this->fw->camelcase(str_replace('-','_',$args['action']));
			if (method_exists($this,$method))
				$this->{$method}($this->fw,$args);
			else
				$this->fw->error(405);
		} else
			$this->fw->error(400,'no action was defined');
	}

	/**
	 * load page content snippets
	 * @param $pageId
	 * @return \Sugar\Content\Model\Content
	 */
	function load($pageId) {
		$db = Storage::instance()->get($this->db);
		return new \Sugar\Content\Model\Content($pageId,$db);
	}

	/**
	 * load global content snippets
	 * @return \Sugar\Content\Model\Content
	 */
	function loadGlobalContent() {
		return new \Sugar\Content\Model\Content('global',$this->db);
	}

	/**
	 * get a list of existing snippets within a flow
	 */
	function snippets(\Base $f3, $args) {
		$data = [
			'snippets' => []
		];
		if ($this->fw->exists('GET.flow',$flow)) {
			$content_service = new ContentService($this->model);
			$data['snippets'] = $content_service->getSnippets($flow);
		}
		$this->view->set('payload',$data);
	}


	/**
	 * save page contents
	 */
	function save(\Base $f3, $args) {
		if ($this->fw->get('VERB') == 'POST') {
			$data = $this->fw->get('POST');

			$content_service = new ContentService($this->model);
			$content_service->saveSnippets($data);

			$this->fw->status(200);
			return;

		} else {
			$this->fw->error(400);
		}
	}

	/**
	 * delete a content element
	 * @param $cId
	 */
	function deleteSnippet($cId) {

		if ($this->fw->exists('POST.flow',$flow)
			&& $this->fw->exists('POST.snippet',$cId)) {

			$content_service = new ContentService($this->model);

			if ($content_service->deleteSnippet($cId)) {
				$this->fw->status(200);
			} else {
				$this->fw->status(404);
			}
		} else {
			$this->fw->error(400,'You need to specify flow and snippets parameter');
		}
	}


	/**
	 * order snippets
	 */
	function orderSnippets(\Base $f3, $args) {
		if ($this->fw->exists('POST.flow',$flow)
			&& $this->fw->exists('POST.snippets',$snippets)) {

			$content_service = new ContentService($this->model);
			$content_service->orderSnippets($flow,json_decode($snippets));
		}
	}


	/**
	 * @param string $cId
	 * @param string $flow
	 * @param array $data
	 */
	function updateSnippetScope($cId,$flow,$scope,$label=NULL) {
		if ($scope == 'global') {
			$global_content = new \Sugar\Content\Model\Content('global',$this->db);
			$content = new \Sugar\Content\Model\Content($this->page->_id,$this->db);
			if ($content->exists($cId)) {
				$ce = $content->get($cId);
				$ce['scope'] = 'global';
				$ce['global_id'] = $cId;

				$content->set($cId,$ce);
				$content->save();

				$ce['global_label'] = $label;
				//				$content->clear($cId);
				unset($ce['global_id']);

				$global_content->initBackup();
				$global_content->set($cId,$ce);
				$global_content->version = 'master';
				$global_content->save();
				$global_content->commitBackup();

				//			foreach ($data as $key => $val)
				//				$ce['settings'][$key] = $val;

				//			$content->set($cId,$ce);
				//			$content->save();
			}
		}
		//		return $content;
	}

	/**
	 * return available snippet types
	 */
	function snippetTypes() {

		$contentService = new ContentService($this->model);

		if (!$this->fw->exists('GET.flow',$flow))
			$flow = NULL;

		$data = $contentService->snippetTypes($flow);

		$this->view->set('payload',['snippet_types'=>$data]);
	}

	function globalSnippets() {
		$data = [];
//		$content = $this->content->loadGlobalContent();
//		$flow = $this->fw->get('GET.flow');
//		if ($content->valid())
//			foreach ($content as $cId => $ce) {
//				if (!is_array($ce)) continue;
//				$data[] = [
//					'id' => $cId,
//					'global_id' => $cId,
//					'global_label' => $ce['global_label'],
//					'flow' => $ce['flow'],
//					'scope' => $ce['scope'],
//					'settings' => $ce['settings'],
//					'type' => $ce['type'],
//				];
//			}

		$this->view->set('payload',['snippets'=>$data]);
	}

	/**
	 * add snippet
	 * @param \Base $f3
	 * @param $args
	 */
	function addSnippet(\Base $f3, $args) {
		$data = [];

		if ($this->fw->exists('POST.flow',$flow)
			&& $this->fw->exists('POST.snippet_type',$type)) {

			$contentService = new ContentService($this->model);
			$snippet = $contentService->addSnippet($type,$flow);

			$this->tmpl_renderer->set('content',$this->load($args['pid']));
			$this->tmpl_renderer->setTemplate('templates/snippets/'.$type.'.html');
			$this->tmpl_renderer->set('ce',$snippet);
			$html = $this->tmpl_renderer->render();
			$data['html'] = $html;

			$this->view->set('payload',$data);
		} else {
			$this->fw->error(400);
		}

	}


	/**
	 * update snippet settings
	 * @param \Base $f3
	 * @param $args
	 */
	function updateSnippetSettings(\Base $f3, $args) {
		if ($this->fw->exists('REQUEST.flow',$flow)
			&& $this->fw->exists('REQUEST.snippet',$cId)) {
			$out=[];

			$contentService = new ContentService($this->model);
			if ($this->fw->VERB == 'GET')
				$out['fields'] = $contentService->getSnippetSettings($cId,$flow);

			elseif ($this->fw->VERB == 'POST') {
				$data = $this->fw->get('POST');
				unset($data['snippet'],$data['flow']);
				$contentService->setSnippetSettings($cId,$flow,$data);
				$content_element = $this->model->get($cId);

				// render snippet
				$this->tmpl_renderer->set('content',$this->load($args['pid']));
				$this->tmpl_renderer->setTemplate('templates/snippets/'.$content_element['type'].'.html');
				$this->tmpl_renderer->set('ce',$content_element);
				$html = $this->tmpl_renderer->render();
				$out['html'] = $html;

			}
			$this->view->setData([
				'payload'=>$out,
				'status'=>'success'
			]);

		}
	}

	/**
	 * change snippet scope local/global
	 * @param \Base $f3
	 * @param $args
	 */
	function changeSnippetScope(\Base $f3, $args) {
		if ($this->fw->exists('POST.flow',$flow)
			&& $this->fw->exists('POST.snippet',$cId)
			&& $this->fw->exists('POST.scope',$scope)
		) {
			if (!$this->fw->exists('POST.label',$label))
				$label=NULL;
			$contentService = new ContentService($this->model);
			$contentService->updateSnippetScope($cId,$flow,$scope,$label);
		}
	}


	/**
	 * handle image action
	 * @param \Base $f3
	 * @param $args
	 */
	function image(\Base $f3, $args) {
		if (isset($args['action'])) {

			$method = 'image_'.$this->fw->camelcase(str_replace('-','_',$args['action']));
			if (method_exists($this,$method))
				$this->{$method}($f3, $args);
			else
				$this->fw->error(405);
		} else
			$this->fw->error(400);
	}

	/**
	 * accept file uploads
	 * @param \Base $f3
	 * @param $params
	 */
	function image_upload(\Base $f3, $args) {
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

				$path = $files[0];
				$file = pathinfo($path);
				$new_path = $file['basename'];
				$this->files->load('uploads/'.$file['basename']);
				$this->files->move($new_path);
				$this->files->file=$new_path;
				$this->files->save();
				$sk=$this->files->filesystem()->getStorageKey();
				$spath=str_replace('local:','',$sk);
				$img = new \Image($new_path,false,$spath);
				$this->view->set('file',[
					'name'=>basename($new_path),
					'path'=>$spath.$new_path,
					'width'=>$img->width(),
					'height'=>$img->height()
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

	/**
	 * list all images
	 */
	function image_collection(\Base $f3, $args) {
		$files = $this->files->filesystem()->listDir();
		$out = [];
		foreach ($files as $filename=>$item) {
			if ($item['type'] == 'file' && in_array($item['extension'],['jpg','png'])) {
				$img = new \Image($item['path']);
				$out[] = [
					'name'=>$filename,
					'path'=>$item['path'],
					'width'=>$img->width(),
					'height'=>$img->height()
				];
			}
		}

		$this->view->set('status','success');
		$this->view->set('files',$out);
	}



	/**
	 * get page list
	 * @param \Base $f3
	 * @param $args
	 * @return bool
	 */
	function page(\Base $f3, $args) {

		if (isset($args['action'])) {
			if ($args['action'] == 'collection') {
				$results = $this->page->findPages();
				$this->view->set('pages', $results ? $results->castAll(0) : []);
			}
			else
				$this->fw->error(405);
		} else
			$this->fw->error(400);
	}


//
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