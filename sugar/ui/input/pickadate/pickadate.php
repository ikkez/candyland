<?php

namespace Sugar\UI\Input\Pickadate;

use Sugar\Component;
use Sugar\Utility\UI;

class Pickadate extends Component {

	function init() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		$assets = \Assets::instance();
		$assets->addCss("src/themes/classic.css",5,'head','datepicker');
		$assets->addCss("src/themes/classic.date.css",5,'head','datepicker');

		$assets->addJs("src/picker.js",5,'footer','datepicker');
		$assets->addJs("src/picker.date.js",5,'footer','datepicker');
		$assets->addJs("src/picker.time.js",5,'footer','datepicker');

		$ui = UI::instance();
		foreach ($this->fw->split($this->fw->LANGUAGE) as $lang) {
			if ($ui->uiPath($path=('src/translations/'.str_replace('-','_',$lang).'.js'), true)) {
				$assets->addJs($path,5,'footer','datepicker');
				break;
			}
		}
		$assets->addJs("component.js",5,'footer','datepicker');
	}

}