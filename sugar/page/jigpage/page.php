<?php

namespace Sugar\Page\JigPage;

use Sugar\Component;
use Sugar\Page\JigPage\Model\Content;
use Sugar\Service\RouteHandler;
use Sugar\Storage;

class Page extends Component {

	/** @var \Sugar\Page\JigPage\Model\Page */
	protected $page;

	function loadRoutes(RouteHandler $routehandle) {
		$pages = new Model\Page();

		$contentPages = $pages->find();
		foreach ($contentPages as $page) {
			if (isset($this->fw['page']['types'][$page->type]))
				$routehandle->route('GET|POST @'.$page->alias.': /'.$page->slug,
					$this->fw['page']['types'][$page->type]['ctrl']);
		}
	}

	function findPages() {
		$page = new Model\Page();
		return $page->find(['deleted_at = ?',null],['order'=>'title']);
	}

	/**
	 * load page
	 * @param int $id
	 * @return bool|Model\Page
	 */
	function load($id) {
		$page = new Model\Page();
		$page->load(['_id = ?',$id]);
		if ($page->valid()) {
			$this->page = $page;
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
		$page = new Model\Page();
		$page->loadByAlias($alias);
		if ($page->valid()) {
			$this->page = $page;
			return $this->page;
		}
		return false;
	}


}