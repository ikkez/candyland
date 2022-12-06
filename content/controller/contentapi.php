<?php

namespace Sugar\Content\Controller;

use Sugar\Content\Model\Content;
use Sugar\Content\Service\ContentService;
use Sugar\Service\Factory;
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

	protected ?ContentService $content_service = null;

	function __construct(TemplateInterface $tmpl_renderer) {
		$this->tmpl_renderer = $tmpl_renderer;
	}

	function beforeroute(\Base $f3,$args) {
		$this->view = new JSON();

		if (isset($args['id'])) {
			$this->pid = $args['id'];

            if (!empty($this->config['contentService']['getActiveVersionHandler'])) {
                $version = Factory::instance()->call($this->config['contentService']['getActiveVersionHandler'], [$this->pid]);
            } else {
                $version = 'master';
            }
            $this->model = $this->load($this->pid, $version);
            $config = isset($this->config['contentService']) && is_array($this->config['contentService'])
                ? $this->config['contentService'] : [];
            $this->content_service = new ContentService($this->model, $this->pid,$config);
		}
		else
			$f3->error(400,'id parameter required');
	}

	function afterroute(\Base $f3,$args) {
		$this->view->dump();
	}

    public function publish(\Base $f3,$args) {
        $this->content_service->publish();
        Factory::instance()->call('ContentHandler.setActivePageVersionForUser', [$args['id'], 'master']);
    }

    public function draft(\Base $f3,$args) {
        $this->content_service->draft();
//        Factory::instance()->call('ContentHandler.setActivePageVersionForUser', [$args['id'], 'master']);
    }

    public function loadVersion(\Base $f3,$args) {
        if (!$f3->devoid('POST.version', $version)) {
            Factory::instance()->call('ContentHandler.setActivePageVersionForUser', [$args['id'], $version]);
        }
    }

    public function getVersions(\Base $f3,$args) {
        $page_versions = [];
        $all = $this->model->find(null, ['order'=>'version SORT_DESC']);
        foreach ($all ?: [] as $pageContents) {
            if ($pageContents->get('version') !== 'master') {
                if ($pageContents->exists('created_at')) {
                    $label = $f3->format('{0,date}, {0,time}', $pageContents->get('created_at')).' - '.$pageContents->get('created_by');
                } else {
                    $label = $f3->format('{0,date}, {0,time}', $pageContents->get('version'));
                }
                if ($pageContents->exists('label')) {
                    $label = '['.$pageContents->get('label').'] '.$label;
                }
            } else {
                $label = '→ master';
                if ($pageContents->exists('created_at')) {
                    $label.= ' - '.$f3->format('{0,date}, {0,time}', $pageContents->get('created_at'));
                }
            }
            $page_versions[] = [
                'label' => $label,
                'tag' => $pageContents->exists('label') ? $pageContents->get('label') : null,
                'version' => $pageContents->get('version'),
                'created_at' => $pageContents->exists('created_at') ? $pageContents->get('created_at') : null,
                'created_by' => $pageContents->exists('created_by') ? $pageContents->get('created_by') : null,
            ];
        }
        if (!empty($this->config['contentService']['getActiveVersionHandler'])) {
            $version = Factory::instance()->call($this->config['contentService']['getActiveVersionHandler'], [$this->pid]);
        } else {
            $version = 'master';
        }
        $this->view->set('active_version', $version);
        $this->view->set('page_versions', $page_versions);
    }

    /**
     * load page content snippets
     */
	function load(string $pageId, ?string $version = null): Content
    {
		$db = Storage::instance()->get($this->db);
		return new \Sugar\Content\Model\Content($pageId,$db, $version);
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
			$data['snippets'] = $this->content_service->getSnippets($flow);
		}
		$this->view->set('payload',$data);
	}


	/**
	 * save page contents
	 */
	function save(\Base $f3, $args) {
		if ($this->fw->get('VERB') == 'POST') {
			$data = $this->fw->get('POST');

            $this->content_service->saveSnippets($data);

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

			if ($this->content_service->deleteSnippet($cId)) {
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

            $this->content_service->orderSnippets($flow,json_decode($snippets));
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

				$global_content->initNewVersion();
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

		if (!$this->fw->exists('GET.flow',$flow))
			$flow = NULL;

		$data = $this->content_service->snippetTypes($flow);

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

			$snippet = $this->content_service->addSnippet($type,$flow);

            if (!empty($this->config['contentService']['getActiveVersionHandler'])) {
                $version = Factory::instance()->call($this->config['contentService']['getActiveVersionHandler'], [$this->pid]);
            } else {
                $version = 'master';
            }

			$this->tmpl_renderer->set('content',$this->load($this->pid, $version));
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

			if ($this->fw->VERB == 'GET')
				$out['fields'] = $this->content_service->getSnippetSettings($cId,$flow);

			elseif ($this->fw->VERB == 'POST') {
				$data = $this->fw->get('POST');
				unset($data['snippet'],$data['flow']);
                $this->content_service->setSnippetSettings($cId,$flow,$data);
				$content_element = $this->model->get($cId);

                if (!empty($this->config['contentService']['getActiveVersionHandler'])) {
                    $version = Factory::instance()->call($this->config['contentService']['getActiveVersionHandler'], [$this->pid]);
                } else {
                    $version = 'master';
                }
				// render snippet
				$this->tmpl_renderer->set('content',$this->load($this->pid, $version));
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
            $this->content_service->updateSnippetScope($cId,$flow,$scope,$label);
		}
	}

}
