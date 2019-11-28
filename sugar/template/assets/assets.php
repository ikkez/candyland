<?php

namespace Sugar\Template\Assets;

use Sugar\Component;

class Assets extends Component {

	use Component\Traits\Installable;

	protected $assets;

	protected $tmpl;

	/**
	 * Assets constructor.
	 * @param \Sugar\View\Template $tmpl
	 */
	function __construct(\Sugar\View\Template $tmpl) {
		$this->tmpl = $tmpl;
	}

	function init() {
		if ($this->config['loadDefaultConfig']) {
			if ($this->fw->exists('ASSETS')) {
				$this->fw->copy('ASSETS','ASSETS_init');
			}
			$this->fw->config($this->fw->fixslashes(__DIR__.'/assets.ini'));

			// configure app-specific temp files folder
			if ($this->config['tempFilesWithinAppUI'] && !$this->fw->devoid('APP.UI',$appUI)) {
				if (is_array($appUI))
					$appUI = $appUI[0];
				$this->fw->set('ASSETS.public_path', $this->fw->get('APP.PATH').$appUI.'compressed/');
			}
			else
				$this->fw->concat('ASSETS.public_path', $this->fw->get('CORE.active_app.key').'/');

			// overwrites / merge with existing config values
			if ($this->fw->exists('ASSETS_init',$conf)) {
				$this->fw->extend('ASSETS_init','ASSETS',true);
				$this->fw->copy('ASSETS_init','ASSETS');
				$this->fw->clear('ASSETS_init');
			}
		}

		\Registry::clear('Assets');

		if ($this->fw->devoid('ASSETS.onFileNotFound')) {
			$this->fw->set('ASSETS.onFileNotFound',function($filePath) {
				$this->broadcast('log.warning',['msg'=>'File not found: "'.$filePath.'"'],$this);
			});
		}

		// register asset plugins
		$this->assets = \Assets::instance($this->tmpl->engine());

		if ($this->config['enableSASS']) {
			\Assets\Sass::instance()->init();

			if ($this->config['loadDefaultConfig']) {
				$cssFilter=$this->fw->get('ASSETS.filter.css');
				if (!is_array($cssFilter))
					$cssFilter=[$cssFilter];
				array_unshift($cssFilter,'sass');
				$this->fw->set('ASSETS.filter.css', $cssFilter);
			}
		}

		if ($this->config['externalMinifierJS']=='matthiasmullie/minify')
			$this->fw->set('ASSETS.minify.compiler.js', function($fileName,$path){
				$minifier = new \MatthiasMullie\Minify\JS($path.$fileName);
				return $minifier->minify();
			});
		elseif ($this->config['externalMinifierJS']=='tedivm/jshrink')
			$this->fw->set('ASSETS.minify.compiler.js', function($fileName,$path){
				return \JShrink\Minifier::minify(\Base::instance()->read($path.$fileName));
			});
	}

	/**
	 * return plugin
	 * @return \Assets
	 */
	public function assets() {
		return $this->assets;
	}
}