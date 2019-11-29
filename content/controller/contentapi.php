<?php

namespace Sugar\Content\Controller;

use Sugar\Content\Service\ContentService;
use Sugar\Storage;
use Sugar\View\JSON;
use Sugar\Component;
use Sugar\View\TemplateInterface;

class ContentAPI extends Component {

	/** @var \Sugar\View\TemplateInterface */
	protected $tmpl_renderer;

	/** @var JSON */
	protected $view;

	/** @var \Sugar\Content\Model\Content */
	protected $model;

	protected $db;

	/** @var \Sugar\Page\JigPage\Page */
	protected $page;

	protected $pid;

	function __construct(TemplateInterface $tmpl_renderer) {
		$this->tmpl_renderer = $tmpl_renderer;
	}

	function beforeroute(\Base $f3,$args) {
		$this->view = new JSON();

		if (isset($args['id'])) {
			$this->pid = $args['id'];
			$this->model = $this->load($args['id']);
		}
		else
			$f3->error(400,'id parameter required');
	}

	function afterroute(\Base $f3,$args) {
		$this->view->dump();
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
//	function loadGlobalContent() {
//		return new \Sugar\Content\Model\Content('global',$this->db);
//	}

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

			$this->emit('saved',['data'=>$data,'pid'=>$this->pid],$this->model);

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
			$global_content = $this->load('global');
			$content = $this->load($this->page->_id);
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

			$this->tmpl_renderer->set('content',$this->load($args['id']));
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
				$this->tmpl_renderer->set('content',$this->load($args['id']));
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

}