<?php

namespace Sugar\Auth\Service;


class JWT extends \Sugar\Component implements AuthServiceInterface {

	/**
	 * @var mixed
	 */
	protected $auth_value;

	/**
	 * @var array
	 */
	protected $opt;

	protected $auth_token;
	protected $jwt;

	protected $private_key;
	protected $public_key;

	function init() {
		$this->opt=array_replace_recursive([
			// in seconds
			'expiration' => 3600,
			// 'HS256', 'HS384', 'HS512', 'RS256'
			'algorithm' => 'HS256',
			'private_key' => '',
			'private_key_pass' => '',
			'public_key' => '',
			// in seconds
			'not_before' => 0,
			'issuer' => $this->fw->HOST,
			'audience' => $this->fw->HOST,
			'cookie_auth' => FALSE,
		],$this->config);

		if ($this->opt['algorithm'] == 'RS256') {
			if (!empty($this->opt['public_key']))
				$this->public_key = openssl_pkey_get_public('file://'.$this->opt['public_key']);
			if (!empty($this->opt['private_key']))
				$this->private_key = openssl_pkey_get_private('file://'.$this->opt['private_key'], $this->opt['private_key_pass']);
		} else {
			$this->private_key = $this->public_key = $this->opt['private_key'];
		}
	}

	/**
	 * check if a user is logged in
	 * @return bool
	 */
	function isAuthenticated() {
		$token=$this->getRequestToken();
		if ($token) {
			$this->auth_token = $token;
			$this->auth_value = $token->sub;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * get authentication value, user or entity data
	 * @return mixed
	 */
	function getAuthValue() {
		return $this->auth_value ?: false;
	}

	/**
	 * get authentication token
	 * @return bool
	 */
	function getAuthToken() {
		return $this->auth_token ?: false;
	}

	/**
	 * @param $token
	 */
	function setAuthToken($token) {
		$this->auth_token = $token;
		if ($this->opt['cookie_auth']) {
			$this->fw->set('COOKIE.auth_token',$token,$this->opt['expiration']);
		}
	}

	/**
	 * get JWT
	 * @return bool
	 */
	function getJWT() {
		return $this->jwt ?: false;
	}

	/**
	 * authenticated as a specific user/entity
	 * @param $auth_value
	 * @param array|null $meta
	 */
	function loginAs($auth_value,$meta=NULL) {
		$this->auth_value = $auth_value;
		$token = $this->generateToken($auth_value,$meta);
		$this->setAuthToken($token);
	}

	/**
	 * remove authentication flag
	 */
	function logout() {
		if ($this->opt['cookie_auth']) {
			$this->fw->clear('COOKIE.auth_token');
		}
	}

	/**
	 * generate new token
	 * @param string $sub
	 * @param array $meta
	 * @return string
	 */
	function generateToken($sub,$meta=NULL) {
		$jwt=[
				// the issuer of the token
				'iss'=>$this->opt['issuer'],
				// the audience of the token
				'aud'=>$this->opt['audience'],
				// the time the JWT was issued. Can be used to determine the age of the JWT
				'iat'=>time(),
				// defines the time before which the JWT MUST NOT be accepted for processing
				'nbf'=>time()+$this->opt['not_before'],
				// this will define the expiration in NumericDate value. The expiration MUST be after the current date/time.
				'exp'=>time()+$this->opt['expiration'],
				// subject of the token
				'sub'=>$sub,
			]+($meta?:[]);
		return \Firebase\JWT\JWT::encode($jwt,$this->private_key,$this->opt['algorithm']);
	}

	/**
	 * decode and verify a token
	 * @param $token
	 * @return bool|object
	 */
	function decodeToken($token) {
		try {
			return \Firebase\JWT\JWT::decode($token,$this->public_key,[$this->opt['algorithm']]);
		} catch (\Exception $e) {
			return FALSE;
		}
	}

	/**
	 * decode token from request header
	 * @return bool|object
	 */
	function getRequestToken() {
		$out = false;
		if (
			!$this->fw->devoid('HEADERS.Authorization', $auth) ||
			!$this->fw->devoid('SERVER.REDIRECT_HTTP_AUTHORIZATION', $auth)
		) {
			$jwt=str_replace('Bearer ','',$auth);
			if (strlen($jwt) > 1) {
				$this->jwt = $jwt;
				$out = $this->decodeToken($jwt);
			}
		} elseif($this->opt['cookie_auth'] && $this->fw->exists('COOKIE.auth_token',$jwt)) {
			$this->jwt = $jwt;
			$out = $this->decodeToken($jwt);
		}
		return $out;
	}
}