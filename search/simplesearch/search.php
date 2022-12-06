<?php

namespace Sugar\Search\SimpleSearch;

use Sugar\Model\Base;

class Search extends \Sugar\Component {

	/** @var Base */
	protected $model;

	protected
		$limit = 10,
		$page = 0,
		$persist_key = 'default',
		$results;	// array of data mapper

	public function setModel(Base $model) {
		$this->model = $model;
	}

	/**
	 * execute search
	 * @param bool $persist
	 * @return array
	 */
	public function exec($persist=true) {
		if ($this->page === 0) {
			// start page of search is requested so reset the search
			$this->fw->clear('SEARCH.'.$this->persist_key);
			if ($persist)
				$this->clear();
		} else {
			$this->fw->set('SEARCH.'.$this->persist_key.'.searchpage',$this->page);
			$this->restore();
		}

		$filter = [];
		$active_filter = [];
		$option = null;

		$out = $this->emit('exec',[
			'active_filter' => $active_filter,
			'filter' => $filter,
			'option' => $option,
		],$this->model);

		$active_filter = $out['active_filter'];
		$filter = $out['filter'];
		$option = $out['option'];

		$this->fw->set('active_filter', $active_filter);
		$this->fw->copy('active_filter','SEARCH.'.$this->persist_key.'.activefilter');
		if ($persist)
			$this->fw->copy('active_filter','SESSION.'.$this->persist_key.'.activefilter');

		// exec search
		$out = $this->model->paginate($this->page-1, $this->limit, $filter, $option);
		$this->results = $out;

		return $this->results;
	}

	/**
	 * get meta details about the last active search
	 * @param string $persist_key
	 * @return array
	 */
	static function getActiveSearchMeta($persist_key='default') {
		/** @var \Base $f3 */
		$f3 = \Base::instance();
		return [
			'filter_data' => $f3->exists('SESSION.'.$persist_key.'.searchfilter', $filter)
				? $filter : [],
			'filter_label' => $f3->exists('SESSION.'.$persist_key.'.activefilter',$label)
				? $label : [],
		];
	}

	/**
	 * run a new search
	 * @param bool $persist
	 */
	public function run($persist=true) {
		$this->fw->copy('POST','SEARCH.'.$this->persist_key.'.searchfilter');
		if ($this->fw->AJAX) {
			$page = 0;
			if ($this->fw->exists('PARAMS.page'))
				$page = $this->fw->get('PARAMS.page');
			if ($this->fw->exists('SEARCH.'.$this->persist_key.'.searchfilter.page'))
				$page = $this->fw->get('SEARCH.'.$this->persist_key.'.searchfilter.page');
			$this->setPage($page);
			$this->exec(false);
		} else {
			if ($persist)
				$this->persist();
			$this->fw->reroute([$this->config['alias']['page'],['page'=>1]]);
		}
	}

	/**
	 * restore last active search from session
	 */
	public function restore() {
		// search page X is requested
		if ($this->fw->exists('SESSION.'.$this->persist_key.'.searchfilter')) {
			$this->fw->copy('SESSION.'.$this->persist_key.'.searchfilter','POST');
		}
	}

	/**
	 * persist current active search to session ( or clear it if it's empty )
	 */
	public function persist() {
		if ($this->fw->devoid('SEARCH.'.$this->persist_key)) {
			$this->clear();
		} else {
			$this->fw->copy('SEARCH.'.$this->persist_key.'.activefilter',
				'SESSION.'.$this->persist_key.'.activefilter');
			$this->fw->copy('SEARCH.'.$this->persist_key.'.searchfilter',
				'SESSION.'.$this->persist_key.'.searchfilter');
			$this->fw->copy('SEARCH.'.$this->persist_key.'.searchpage',
				'SESSION.'.$this->persist_key.'.searchpage');
		}
	}

	public function clear() {
		$this->fw->clear('SESSION.'.$this->persist_key.'.activefilter');
		$this->fw->clear('SESSION.'.$this->persist_key.'.searchfilter');
		$this->fw->clear('SESSION.'.$this->persist_key.'.searchpage');
	}

	/**
	 * return existing filter key
	 * @param $key
	 * @return bool
	 */
	public function getFilterKey($key) {
		if (!$this->fw->exists('SESSION.'.$this->persist_key.'.searchfilter.'.$key,$value))
			$value = false;
		return $value;
	}

	/**
	 * set page position
	 * @param $position
	 */
	function setPage($position) {
		$this->page = (int) $position;
	}

	/**
	 * set results limit
	 * @param $max
	 */
	function setLimit($max) {
		$this->limit = (int) $max;
	}

	/**
	 * return search results
	 * @return mixed
	 */
	function getResults() {
		return $this->results;
	}

}
