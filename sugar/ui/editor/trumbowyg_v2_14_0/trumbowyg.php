<?php

namespace Sugar\UI\Editor\Trumbowyg_v2_14_0;

use Sugar\Component;

class Trumbowyg extends Component {

	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'src/');

		\Assets::instance()->addCss("dist/ui/trumbowyg.min.css",5,'head','trumbowyg');

		\Assets::instance()->addJs('dist/trumbowyg.min.js',6,'footer','trumbowyg');
		\Assets::instance()->addJs("dist/plugins/history/trumbowyg.history.min.js",5,'footer','trumbowyg');
		\Assets::instance()->addJs("dist/langs/de.min.js",5,'footer','trumbowyg');

	}

}