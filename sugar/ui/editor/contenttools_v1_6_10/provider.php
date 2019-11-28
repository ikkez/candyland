<?php

namespace Sugar\UI\Editor\ContentTools_v1_6_10;

use Sugar\Component;

class Provider extends Component {

	function enableEditor() {
		$this->fw->set('SESSION.contenttools_enabled',TRUE);
	}

	function isEnabled() {
		return ($this->fw->exists('SESSION.contenttools_enabled',$val) && $val === TRUE);
	}

	function disableEditor() {
		$this->fw->clear('SESSION.contenttools_enabled');
	}

}