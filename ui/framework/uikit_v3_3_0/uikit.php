<?php

namespace Sugar\UI\Framework\UIkit_v3_3_0;

use Sugar\Component;

class UIkit extends Component {

	protected
		$watch_files=false,
		$file_base,
		$file_variables;

	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		if ($this->file_base) {
			$opt=NULL;
			if ($this->watch_files)
				$opt = [
					'watch'=>$this->watch_files
				];
			\Assets::instance()->add($this->file_base,'css','head',8,NULL, $opt);
			\Assets::instance()->addJs('dist/js/uikit.min.js',8,'footer','top');
			\Assets::instance()->addJs('dist/js/uikit-icons.min.js',7,'footer','top');
		}
	}

}