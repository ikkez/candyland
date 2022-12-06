<?php

namespace Sugar\Content\Service;

use Sugar\Content\Controller\Snippet;
use Sugar\Content\Model\Content;
use Sugar\Service\Factory;

class ContentService {

	/** @var \Sugar\Content\Model\Content */
	protected $model;

    protected array $config;

    /** @var string | int */
    protected $pageId;

	function __construct(Content $model, $pageId, array $config=[]) {
		$this->model = $model;
        $this->config = $config;
        $this->pageId = $pageId;
	}

	/**
	 * get a list of existing snippets within a flow
	 */
	function getSnippets($flow) {
		$data = [];
		$raw = $this->model->cast();
		$types = $this->getSnippetConfigs();
		foreach ($raw as $id => $elm) {
			if (is_array($elm) && $flow == $elm['flow']) {
				$snippet = $types[$elm['type']];
				$data[] = [
					'id' => $elm['id'],
					'type' => $elm['type'],
					'label' => $snippet['label'],
					'scope' => $elm['scope'],
					'settings' => $elm['settings'],
					'image_url' => $snippet['image_url'],
				];
			}
		}
		return $data;
	}

    protected function initNewVersion() {
        $this->model->initNewVersion();
        if (!empty($this->config['versionHandler'])) {
            Factory::instance()->call($this->config['versionHandler'], [$this->model, $this->pageId]);
        }
    }

    public function publish() {
        if ($this->model->get('version') === 'master') {
            return;
        }
        // when publishing new master, patch version of existing master
        $latestMaster = $this->model->findone(['@version = ?', 'master']);
        if ($latestMaster) {
            if ($latestMaster->exists('created_at')) {
                $latestMaster->set('version', $latestMaster->get('created_at'));
            } else {
                $latestMaster->set('version', time());
            }
            $latestMaster->set('label', 'Old Master');
            $latestMaster->save();
        }

        $this->initNewVersion();
        $this->model->set('version', 'master');
        $this->model->save();
        $this->model->commitBackup();
    }

    public function draft() {
        $this->model->set('label', 'draft');
        $this->model->save();
    }

	/**
	 * save snippet contents
	 * @param $snippets_conf
	 */
	function saveSnippets($snippets_conf) {

		$this->initNewVersion();

		foreach($snippets_conf as $ckey => $cval) {
			$ckey_ex = explode(':',$ckey,2);
			$cId = $ckey_ex[0];
			$cField = isset($ckey_ex[1]) ? $ckey_ex[1] : 'content';
			if ($this->model->exists($cId)) {
				$elm = $this->model->get($cId);
				$elm[$cField] = $cval;
				$this->model->set($cId, $elm);
			} else {
				$this->model->set($cId, $cval);
			}
		}

		$this->model->save();
		$this->model->commitBackup();
	}

	/**
	 * delete a content element
	 * @param $cId
	 * @return bool
	 */
	function deleteSnippet($cId) {
		if ($this->model->exists($cId)) {
			$this->initNewVersion();

			$this->model->clear($cId);

			// find related snippets
			foreach ($this->model as $key => $ce) {
				if (is_array($ce) && isset($ce['flow'])
					&& preg_match('/(?:\B|^)'.preg_quote($cId).'(?:\B|$)/i',$ce['flow']))
					$this->model->clear($key);
			}

			$this->model->save();
			$this->model->commitBackup();
			return true;
		}
		return false;
	}

	/**
	 * @param string|int $flow
	 * @param array $ids
	 */
	function orderSnippets($flow,$ids) {

		$this->initNewVersion();
		$order = [];
		foreach ($ids as $cId) {
			if ($this->model->exists($cId)) {
				$order[] = [$cId,$this->model->get($cId)];
				$this->model->clear($cId);
			}
		}
		foreach ($order as $item) {
			$this->model->set($item[0],$item[1]);
		}
		$this->model->save();
		$this->model->commitBackup();
	}

	/**
	 * return snippet settings
	 * @param $cId
	 * @param null $flow
	 * @return array
	 */
	function getSnippetSettings($cId,$flow=NULL) {
		$settings=[];
		if ($this->model->exists($cId)) {
			$ce = $this->model->get($cId);
			$c_settings = $ce['settings'];
			$types = $this->getSnippetConfigs();
			$snippet = $types[$ce['type']];
			if (!empty($snippet['settings'])) {
				$settings = $snippet['settings'];
			}
			foreach ($settings as &$field) {
				foreach ($c_settings as $s_key => $s_val) {
					if ($field['name'] == $s_key) {
						if ($field['type'] == 'boolean')
							$field['value'] = (bool) $s_val;
						else
							$field['value'] = $s_val;
						break;
					}
				}
				unset($field);
			}
		}
		//		$fields = [
		//			[
		//				'type'=> 'boolean',
		//				'name'=> 'boolean_example',
		//				'label'=> 'Boolean example 123',
		//				'required'=> false,
		//				'value'=> true
		//			], [
		//				'type'=> 'select',
		//				'name'=> 'select_example',
		//				'label'=> 'Select example',
		//				'required'=> true,
		//				'value'=> 1,
		//				'choices'=> [[1, 'One'], [2, 'Two'], [3, 'Three']]
		//			], [
		//				'type'=> 'text',
		//				'name'=> 'Text_example',
		//				'label'=> 'Texty example',
		//				'required'=> true,
		//				'value'=> 'foo'
		//			]
		//		];
		return $settings;
	}

	/**
	 * set snippet settings
	 * @param string $cId
	 * @param string $flow
	 * @param array $data
	 * @return bool
	 */
	function setSnippetSettings($cId,$flow,$data) {
		if ($this->model->exists($cId)) {
			$ce = $this->model->get($cId);
			foreach ($data as $key => $val)
				$ce['settings'][$key] = $val;

			$this->model->set($cId,$ce);
			$this->model->save();
			return true;
		}
		return false;
	}

	/**
	 * @param string $cId
	 * @param string $flow
	 * @param $scope
	 * @param null $label
	 */
	function updateSnippetScope($cId,$flow,$scope,$label=NULL) {
		if ($scope == 'global') {
			$global_content = new \Sugar\Content\Model\Content('global',$this->db);
			if ($this->model->exists($cId)) {
				$ce = $this->model->get($cId);
				$ce['scope'] = 'global';
				$ce['global_id'] = $cId;

				$this->model->set($cId,$ce);
				$this->model->save();

				$ce['global_label'] = $label;
				//				$content->clear($cId);
				unset($ce['global_id']);

				$global_content->initNewVersion();
				$global_content->set($cId,$ce);

				$global_content->save();
				$global_content->commitBackup();

	//			foreach ($data as $key => $val)
	//				$ce['settings'][$key] = $val;

	//			$content->set($cId,$ce);
	//			$content->save();
			}
		}
	}

	function getSnippetConfigs() {
		$f3 = \Base::instance();
		$types = $f3->get('CONTENT.types');
		foreach ($types as $type => &$snippet_conf) {
			if (!empty($snippet_conf['allowed_flows'])) {
				if (!is_array($snippet_conf['allowed_flows']))
					$snippet_conf['allowed_flows']=[$snippet_conf['allowed_flows']];
			}
			$snippet_conf['id']=$type;
			if (empty($snippet_conf['settings']))
				$snippet_conf['settings'] = [];
			if (empty($snippet_conf['controller']))
				$snippet_conf['controller'] = '\Sugar\Content\Controller\Snippet';
			unset($snippet_conf);
		}
		return $types;
	}

	/**
	 * get available snippet types for a flow
	 * @param null $flow
	 * @return array
	 */
	function snippetTypes($flow=NULL) {
		$data = [];

		$types = $this->getSnippetConfigs();

		if ($flow) {
			$exp = explode('_',$flow);
			$flow=$exp[0];
			foreach ($types as $type => $snippet_conf) {
				if (!empty($snippet_conf['allowed_flows'])) {
					if (in_array($flow,$snippet_conf['allowed_flows']))
						$data[] = $snippet_conf;
				}
			}
		}
		return $data;
	}

	/**
	 * add a new snippet to a content flow
	 * @param $type
	 * @param $flow
	 * @return bool|array snippet configuration
	 */
	function addSnippet($type,$flow) {
		$id = \Base::instance()->hash(uniqid());

		$types = $this->getSnippetConfigs();

		$additional_snippets = [];

		$avaiable_types = array_keys($types);

		if (!in_array($type,$avaiable_types))
			return FALSE;

		$snippet_conf = $types[$type];

		/** @var Snippet $snippet_ctrl */
		$snippet_ctrl = new $snippet_conf['controller']($id,$flow,$type);

		$snippet = $snippet_ctrl->create($snippet_conf);
		if (method_exists($snippet_ctrl,'add'))
			$additional_snippets = $snippet_ctrl->add();


		$this->saveSnippets([$id=>$snippet]+$additional_snippets);

		return $snippet;
	}


}
