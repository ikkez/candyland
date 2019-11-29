<?php

namespace Sugar\UI\Input\Vue_DatePicker;

use Sugar\Component;

class VueDatePicker extends Component {

	function init() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		\Assets::instance()->addJs("src/dist/vuejs-datepicker.js",5,'footer','datepicker');
		\Assets::instance()->addJs("component.js",5,'footer','datepicker');
	}

}