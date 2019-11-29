<?php

namespace Sugar\Location\Service;

class Google extends \Sugar\Component {

	/** @var \Cache */
	protected $cache;

	protected $api_key;

	protected $location_country;
	protected $location_ttl=2592000;

	protected $maps_temp_dir='images/temp/';
	protected $maps_default_zoom=8;
	protected $maps_default_size='400x300';

	function __construct() {
		if (!$this->cache)
			$this->cache = \Cache::instance();
	}

	/**
	 * find location coordinates based on given address
	 * @param string $addr
	 * @return array|bool
	 */
	function coordinates($addr) {
		$web = \Web::instance();

		$options = [
			'address'=>$addr,
			'key'=>$this->api_key
		];

		if ($this->location_country)
			$options['components']='country:'.$this->location_country;

		$uri = 'https://maps.googleapis.com/maps/api/geocode/json?'.http_build_query($options);

		$hash = 'location_'.$this->fw->hash($uri).'.coord';
		if ($this->location_ttl !== 0 && $this->cache && $this->cache->exists($hash, $val)) {
			return $val;
		} else {
			$result = $web->request($uri);
			if (!empty($result['body'])) {
				$result = json_decode($result['body']);
				if ($result->status == 'OK') {
					$latitude = $result->results[0]->geometry->location->lat;
					$longitude = $result->results[0]->geometry->location->lng;
					$data = [
						'Latitude'=>$latitude,
						'Longitude'=>$longitude
					];
					$this->cache->set($hash,$data,$this->location_ttl);
					return $data;
				} elseif($result->status == "OVER_QUERY_LIMIT") {
					// TODO: broadcast error
				}
			}
			return false;
		}
	}

	/**
	 * @param string $center location address
	 * @param string $size
	 * @param int $zoom
	 * @return string
	 */
	function map($center,$size=null,$zoom=null){
		$map = new \Web\Google\StaticMap();
		// set api key
		if ($this->api_key)
			$map->key($this->api_key);

		if (!$zoom)
			$zoom = $this->maps_default_zoom;

		if (!$size)
			$size = $this->maps_default_size;

		$path = $this->maps_temp_dir;

		if (!is_dir($path))
			@mkdir($path,0777,true);

		$hash = $this->fw->hash($center.$size.$zoom);
		$file = $path.$hash.'.jpg';
		if (!file_exists($file)) {
			$map->center($center);
			$map->markers('color:blue|'.$center); //
			$map->maptype('roadmap');
			$map->size($size);
			$map->zoom($zoom);
			$map->sensor('false');
			$this->fw->write($file, $map->dump());
		}
		return $file;
	}

} 