<?php

namespace Sugar\PDF\WkhtmlToPDF;

use Sugar\Component;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class PDF extends Component {

	protected $binary_path;

	/**
	 * convert given URL and send PDF to client
	 * @param $url
	 * @param $filename
	 */
	function urlToPdf($url,$filename) {
		$snappy = new \Knp\Snappy\Pdf($this->binary_path);
//		$cacheDir = $this->fw->fixslashes($this->fw->ROOT.'/'.$this->fw->TEMP);
//		$snappy->setOption('cache-dir',$cacheDir);

//		$snappy->setTimeout(30);
		$snappy->setOption('encoding',"utf-8");
//		$snappy->setOption('lowquality',FALSE);

		try {
//			if ($this->fw->exists('COOKIE',$cookie)) {
//				$snappy->setOption('cookie', $cookie);
//			}
			$out = @$snappy->getOutput($url);
		} /* catch (ProcessTimedOutException $e) {
			// do nothing
		} */ catch (\Exception $e) {
			\Base::instance()->error($e->getCode(),$e->getMessage(),$e->getTrace());
		}

		if ($out) {
			header('Content-Type: application/pdf');
			if ($this->config['download_attachment'])
				header('Content-Disposition: attachment; filename="'.$filename.'"');
			echo $out;
		}
	}

}