<?php

namespace Sugar\Stats\Google\Analytics;

use Sugar\Component;

class Tracking extends Component {


	function ready() {
		$this->fw->concat('UI',';'.$this->getComponentPath().'ui/');

		if (!empty($this->config['tracking_id'])) {

			\Assets::instance()->addJs('https://www.googletagmanager.com/gtag/js?id='.$this->config['tracking_id']);

			\Assets::instance()->addInline('
				  window.dataLayer = window.dataLayer || [];
				  function gtag(){dataLayer.push(arguments);}
				  gtag(\'js\', new Date());
				  gtag(\'config\', \''.$this->config['tracking_id'].'\');
			','js','footer');
		}

	}

}