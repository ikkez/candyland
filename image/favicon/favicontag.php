<?php


namespace Sugar\Image\FavIcon;

class FavIconTag extends \Template\TagHandler {

	protected $options = [
		'temp_dir' => 'img/',
		'public_path' => '',
		'formats' => [
			'favicon' => [
				'tag' => '<link rel="icon" type="image/png" href="{0}" sizes="{1}" />',
				'sizes' => [16,32,96]
			],
			'apple' => [
				'tag' => '<link rel="apple-touch-icon" sizes="{1}" href="{0}" />',
				'sizes' => [120,180,152,167]
			],
		],
		'quality' => 75,
	];

	/**
	 * constructor.
	 * @param array $options
	 */
	function __construct($options=[]) {
		$this->setOptions($options);
		parent::__construct();
	}

	/**
	 * set options
	 * @param array $args
	 */
	function setOptions(array $args) {
		$this->options=array_replace_recursive($this->options,$args);
	}

	/**
	 * return defined options
	 * @return array|mixed
	 */
	function getOptions() {
		return $this->options;
	}

	/**
	 * build tag string
	 * @param array $attr
	 * @param string $content
	 * @return string
	 */
	function build($attr,$content) {
		$out = '';
		if (isset($attr['href'])) {
			$opt = array(
				'quality'=>$this->options['quality'],
			);
			// merge into defaults
			$opt = array_intersect_key($attr + $opt, $opt);
			// get dynamic path
			$path = preg_match('/{{(.+?)}}/s',$attr['href']) ?
				$this->tmpl->token($attr['href']) : $attr['href'];
			foreach ($this->options['formats'] as $name => $conf) {
				foreach ($conf['sizes'] as $size) {
					if (!is_array($size))
						$size=[$size,$size];
					$opt['width']=$size[0];
					$opt['height']=$size[1];
					$filename = $this->resize($path,$opt);
					if (!empty($this->options['public_path']))
						$icon_path = $this->options['public_path'].$filename;
					else
						$icon_path = $this->options['temp_dir'].$filename;
					$out.= $this->f3->format($conf['tag'],$icon_path,implode('x',$size))."\n";
				}
			}
		}
		return $out;
	}

	/**
	 * on demand image resize
	 * @param $path
	 * @param $opt
	 * @return string filename
	 */
	function resize($path,$opt) {
		$hash = $this->f3->hash($path.$this->f3->serialize($opt));
		$ext = 'png';
		$new_file_name = 'favicon_'.$hash;
		$dst_path = $this->options['temp_dir'];
		if (!file_exists($dst_path.$new_file_name)) {
			$path = explode('/', $path);
			$file = array_pop($path);
			$src_path = implode('/',$path).'/';
			foreach ($this->f3->split($this->f3->UI,FALSE) as $dir)
				if (is_file($dir.$src_path.$file)) {
					$src_path=$dir.$src_path;
					$imgObj = new \Image($file, false, $src_path);
					if (!is_dir($dst_path))
						mkdir($dst_path,0775,true);
					$ow = $imgObj->width();
					$oh = $imgObj->height();
					if (!$opt['width'])
						$opt['width'] = round(($opt['height']/$oh)*$ow);
					if (!$opt['height'])
						$opt['height'] = round(($opt['width']/$ow)*$oh);
					$opt['quality']=max(0,round(((int)$opt['quality'])/10)-1);
					$imgObj->resize((int)$opt['width'], (int)$opt['height'], true, true);
					$file_data = $imgObj->dump('png', $opt['quality']);
					$new_file_name.='_'.$opt['width'].'x'.$opt['height'].'.'.$ext;
					$this->f3->write($dst_path.$new_file_name, $file_data);
					break;
				}
		}
		return $new_file_name;
	}
}