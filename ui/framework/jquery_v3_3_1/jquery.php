<?php

namespace Sugar\UI\Framework\jQuery_v3_3_1;

use Sugar\Component;

class jQuery extends Component {

	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'src/');
		\Assets::instance()->addJs('dist/jquery.min.js',8,'footer','top');
	}

}