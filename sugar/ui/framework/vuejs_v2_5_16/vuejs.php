<?php

namespace Sugar\UI\Framework\VueJS_v2_5_16;

use Sugar\Component;

class VueJS extends Component {

	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'src/');
		\Assets::instance()->addJs('dist/vue.min.js',8,'footer','top');
	}

}