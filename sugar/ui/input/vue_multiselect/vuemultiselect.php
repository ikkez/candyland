<?php

namespace Sugar\UI\Input\Vue_MultiSelect;

use Sugar\Component;

class VueMultiSelect extends Component {

	function init() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		\Assets::instance()->addJs("dist/vue-multiselect.min.js");
		\Assets::instance()->addCss("dist/vue-multiselect.min.css");
		\Assets::instance()->addJs("vue_multiselect_component.js");
	}

}