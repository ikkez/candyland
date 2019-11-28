<?php

namespace Sugar\UI\Editor\ContentTools_v1_6_10;

use Sugar\Component;

class Editor extends Component {

	/** @var Provider */
	protected $provider;

	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		if ($this->provider->isEnabled() && $this->fw->exists('GET.ct_editor',$pid)) {
			//\Assets::instance()->addJs('src/content-tools.min.js');
			\Assets::instance()->addJs('src/content-tools.js');
			\Assets::instance()->addJs('src/content-flow.js');
//			\Assets::instance()->addCss('src/content-tools.min.css');
			\Assets::instance()->addCss('src/content-flow.min.css');
			\Assets::instance()->addJs('js/editor.js');
			\Assets::instance()->addCss('css/ct_content.css');
			\Assets::instance()->addCss('css/editor.scss');
			$this->fw->set('ct_page_id',$pid);
		}

	}

}