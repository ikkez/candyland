<?php

namespace Sugar\Content\Controller;

use Sugar\View\JSON;
use Sugar\Component;

class PageAPI extends Component {

	/** @var JSON */
	protected $view;

	/** @var \Sugar\Page\JigPage\Page */
	protected $page;


	/** @var \Sugar\Content\Controller\ContentAPI */
	public $content;

	function __construct(JSON $view) {
		$this->view = $view;
	}

	function afterroute(\Base $f3,$args) {
		$this->view->dump();
	}

	/**
	 * get page list
	 * @param \Base $f3
	 * @param $args
	 * @return bool
	 */
	function collection(\Base $f3, $args) {
		if ($f3->exists('lang',$lang)) {
			$results = $this->page->findPagesByLang($lang);
		} else {
			$results = $this->page->findPages();
		}
		$this->view->set('pages', $results ? $results->castAll(0) : []);
	}

	/**
	 * get page config meta
	 * @param \Base $f3
	 * @param $args
	 * @return bool
	 */
	function meta(\Base $f3, $args) {
		$this->view->set('config', $f3->get('page'));
		$this->view->set('domains', $f3->get('APP.domain'));
	}

	/**
	 * get page details
	 * @param \Base $f3
	 * @param $args
	 * @return bool
	 */
	function load(\Base $f3, $args) {
		$pageModel = $this->page->load($args['id']);
		$this->view->set('page', $pageModel ? $pageModel->cast() : false);
	}

	/**
	 * get page details
	 * @param \Base $f3
	 * @param $args
	 * @return bool
	 */
	function save(\Base $f3, $args) {
		$pageModel = $this->page->save($args['id'],$f3->get('POST'));
		if ($pageModel) {
			$this->fw->status(200);
			$this->view->set('page', $pageModel->cast());
		} else {
			$this->fw->status(400);
		}
	}

	function create(\Base $f3, $args) {
		$this->page->create($f3->get('POST'));
		$this->fw->status(200);
	}

	function delete(\Base $f3, $args) {
		$this->page->delete($args['id']);
		$this->fw->status(200);
	}


	function createVariant(\Base $f3, $args) {
		$pageModel = $this->page->load($args['id']);
		$new_lang = $f3->get('POST.lang');
		if ($pageModel) {
			$f3->clear('POST');
			$pageModel->copyto('POST');
			$pageModel->reset();
			$f3->clear('POST._id');
			$pageModel->copyfrom('POST');
			$pageModel->lang = $new_lang;
			$pageModel->save();
			$this->fw->status(200);
			$this->view->set('item', $pageModel->valid() ? $pageModel->cast() : false);

			$orig = $this->content->load($args['id']);
			$new = $this->content->load($pageModel->_id);
			$new->copyfrom($orig->cast());
			$new->save();
		} else
			$this->fw->error(404);
	}


}