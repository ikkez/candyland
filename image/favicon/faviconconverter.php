<?php


namespace Sugar\Image\FavIcon;

use Sugar\Component;

class FavIconConverter  extends Component {

	protected $tmpl;

	/**
	 * Assets constructor.
	 * @param \Sugar\View\Template $tmpl
	 */
	function __construct(\Sugar\View\Template $tmpl) {
		$this->tmpl=$tmpl;
	}

	function init() {

		$engine=$this->tmpl->engine();

		if ($engine instanceof \Template) {
			FavIconTag::init('favicon',$engine,[
				'temp_dir' => 'ui/favicon/',
			]);
		}

	}

}