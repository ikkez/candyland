<?php

namespace Sugar\UI\Editor\Trumbowyg_v2_21_0;

use Sugar\Component;

class Trumbowyg extends Component {

	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'src/');
		$dist_dir = 'trumbowyg';

		\Assets::instance()->addCss($dist_dir."/ui/trumbowyg.min.css",5,'head','trumbowyg');
		\Assets::instance()->addCss("trumbowyg-extra.css",5,'head','trumbowyg');

		\Assets::instance()->addJs($dist_dir.'/trumbowyg.min.js',6,'footer','trumbowyg');
		\Assets::instance()->addJs($dist_dir."/plugins/history/trumbowyg.history.min.js",5,'footer','trumbowyg');
		\Assets::instance()->addJs($dist_dir."/langs/de.min.js",5,'footer','trumbowyg');
		\Assets::instance()->addInline("$(function() {
			$.trumbowyg.svgPath = '".$this->getComponentPath()."src/".$dist_dir."/ui/icons.svg';
		});",'js','footer','trumbowyg');

	}

}