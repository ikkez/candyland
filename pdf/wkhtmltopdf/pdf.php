<?php

namespace Sugar\PDF\WkhtmlToPDF;

use Sugar\Component;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class PDF extends Component {

	/** @var string */
	protected $binary_path;

	/**
	 * convert given URL and send PDF to client
	 * @param $url
	 * @param $filename
	 */
	function urlToPdf($url,$filename, $send=TRUE) {
		$snappy = new \Knp\Snappy\Pdf($this->binary_path);
		$snappy->setOption('encoding',"utf-8");

		try {
//			if ($this->fw->exists('COOKIE',$cookie)) {
//				$snappy->setOption('cookie', $cookie);
//			}
			$out = @$snappy->getOutput($url);
			if ($out) {
				if ($send) {
					header('Content-Type: application/pdf');
					if ($this->config['download_attachment'])
						header('Content-Disposition: attachment; filename="'.$filename.'"');
					echo $out;
				} else {
					return $out;
				}
			}
		} catch (\Exception $e) {
			\Base::instance()->error($e->getCode(),$e->getMessage(),$e->getTrace());
		}
	}

	/**
	 * convert html data to PDF and send to client
	 * @param $htmlData
	 * @param $filename
	 */
	function htmlToPdf($htmlData, $filename, $send=TRUE) {
		$snappy = new \Knp\Snappy\Pdf($this->binary_path);
		$snappy->setOption('encoding',"utf-8");
		try {
			$out = @$snappy->getOutputFromHtml($htmlData);
			if ($out) {
				if ($send) {
					header('Content-Type: application/pdf');
					if ($this->config['download_attachment'])
						header('Content-Disposition: attachment; filename="'.$filename.'"');
					echo $out;
				} else {
					return $out;
				}
			}
		} catch (\Exception $e) {
			\Base::instance()->error($e->getCode(),$e->getMessage(),$e->getTrace());
		}
	}


}