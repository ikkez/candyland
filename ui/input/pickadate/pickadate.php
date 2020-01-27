<?php

namespace Sugar\UI\Input\Pickadate;

use Sugar\Component;
use Sugar\Utility\UI;

class Pickadate extends Component {

	function init() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		$assets = \Assets::instance();
		$assets->addCss("src/themes/classic.css",5,'head','pickadate');
		$assets->addCss("src/themes/classic.date.css",5,'head','pickadate');

		$assets->addJs("src/picker.js",5,'footer','pickadate');
		$assets->addJs("src/picker.date.js",5,'footer','pickadate');
		$assets->addJs("src/picker.time.js",5,'footer','pickadate');

		$ui = UI::instance();
		foreach ($this->fw->split($this->fw->LANGUAGE) as $lang) {
			if ($ui->uiPath($path=('src/translations/'.str_replace('-','_',$lang).'.js'), true)) {
				$assets->addJs($path,5,'footer','pickadate');
				break;
			}
		}
		$assets->addJs("pickadate_vue.js",5,'footer','pickadate');
	}

}