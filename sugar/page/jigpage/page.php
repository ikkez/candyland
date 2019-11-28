<?php

namespace Sugar\Page\JigPage;

use Sugar\Component;
use Sugar\Page\JigPage\Model\Content;
use Sugar\Service\RouteHandler;
use Sugar\Storage;

class Page extends Component {

	/** @var \Sugar\Page\JigPage\Model\Page */
	protected $page;

	function __construct() {
		$this->page = new Model\Page();
	}

	function loadRoutes(RouteHandler $routehandle) {

		$contentPages = $this->page->find();
		foreach ($contentPages as $page) {
			if (isset($this->fw['page']['types'][$page->type]))
				$routehandle->route('GET|POST @'.$page->alias.': /'.$page->slug,
					$this->fw['page']['types'][$page->type]['ctrl']);
		}
	}

	function findPages() {
		return $this->page->find(['deleted_at = ?',null],['order'=>'title']);
	}

	function findPagesByLang($lang) {
		return $this->page->find(['deleted_at = ? and lang = ?',null,$lang],['order'=>'title']);
	}

	/**
	 * load page
	 * @param int $id
	 * @return bool|Model\Page
	 */
	function load($id) {
		$this->page->load(['_id = ?',$id]);
		if ($this->page->valid()) {
			return $this->page;
		}
		return false;


//		$versions = $content->versions();
//		$f3->set('page_versions',$versions ?: array('master'));
//		$f3->set('pages', $pageSetting->find());
		// get available page types from config
//		$page_types = $f3->get('page.types');
//		if (isset($page_types[$pageSetting->type])) {
			// execute controller of specific page type
//			$f3->set('ALIAS',$pageSetting->alias);
//			$f3->call($page_types[$pageSetting->type]['ctrl'],array($f3,array()));
//		} else {
//			$f3->error(400,$f3->get('ll.admin.page-form.error.page_type.invalid'));
//		}
	}

	/**
	 * load page
	 * @param string $alias
	 * @return bool|Model\Page
	 */
	function loadByAlias($alias) {
		if (!$this->fw->devoid('GET.lang',$lang)) {
			$this->fw->set('LANGUAGE',$lang);
			$this->fw->set('page.lang',$lang);
		}
//		$this->page->load(['alias = ? and lang = ?',$alias, $this->fw->get('page.lang')]);
		$this->page->load(['alias = ? and lang = ?',$alias, $this->fw->get('page.lang')]);

		if ($this->page->valid()) {
			return $this->page;
		}
		return false;
	}

	/**
	 * save a page
	 * @param $id
	 * @param $data
	 * @return bool|Model\Page
	 */
	function save($id,$data) {
		$this->page->load(['_id = ?',$id]);
		if ($this->page->valid()) {
			$this->page->copyfrom($data);
			if (!$this->page->cid) {
				$this->page->cid = $id;
			}
			$this->page->save();
			return $this->page;
		} else {
			return false;
		}
	}

	/**
	 * create a new page
	 * @param $data
	 * @return bool
	 */
	function create($data) {
		$this->page->reset();
		$this->page->copyfrom($data);
		$this->page->save();
		$this->page->cid = $this->page->_id;
		$this->page->save();
		return true;
	}

	/**
	 * delete an existing page
	 * @return bool
	 */
	function delete($id) {
		$this->page->reset();
		$this->page->load(['_id = ?',$id]);
		if (!$this->page->valid()) {
			$this->fw->error(404);
		}
//			$page->touch('deleted_at');
//			$page->save();
		$this->page->erase();
		return true;
	}


}