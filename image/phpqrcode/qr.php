<?php

namespace Sugar\Image\PhpQrCode;

use Sugar\Component;


class QR extends Component {

	protected $text;

	/**
	 * @param string $text
	 */
	function setText($text) {
		$this->text = $text;
	}

	/**
	 * render QR Code to string
	 * @return string
	 */
	function render() {
		if (!$this->text)
			user_error('No text to render was defined');

		require_once(__DIR__."/src/qrlib.php");

		if ($this->config['error_correction'] == 0)
			$error_correction = QR_ECLEVEL_L;
		elseif ($this->config['error_correction'] == 1)
			$error_correction = QR_ECLEVEL_M;
		elseif ($this->config['error_correction'] == 2)
			$error_correction = QR_ECLEVEL_Q;
		elseif ($this->config['error_correction'] == 3)
			$error_correction = QR_ECLEVEL_H;
		else
			$error_correction = QR_ECLEVEL_L;

		return \QRcode::png($this->text,NULL,$error_correction,$this->config['size'],$this->config['margin']);
	}

	/**
	 * send file to browser
	 */
	function dump() {

		// must-revalidate
		$this->fw->expire(0);
		header("Content-Type: image/png");

		echo $this->render();
	}

}