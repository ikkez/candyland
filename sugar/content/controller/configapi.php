<?php

namespace Sugar\Content\Controller;

use Controller\BackendUserAPI;
use Sugar\Auth\Service\JWT;
use Sugar\Service\Factory;
use Sugar\View\JSON;
use Sugar\Component;
use Sugar\Auth\SimpleAuth;
use Model\BackendUser;

class ConfigAPI extends Component {

	/** @var JSON */
	protected $view;

	/** @var SimpleAuth $auth */
	protected $auth;

	/** @var JWT $authService */
	protected $authService;

	/** @var SimpleAuth $backendAuthService */
	protected $backendAuthService;

	/** @var BackendUserAPI */
	protected $user;

	/** @var array handle request types */
	protected $types=array();

	protected $auth_header;

	function beforeroute(\Base $f3,$params) {
		if (!$this->authService->isAuthenticated() && $this->fw->ALIAS != 'config_api_login') {
			$this->fw->error(403);
		}
	}

	/**
	 * handle incoming API request
	 * @param \Base $f3
	 * @param $args
	 */
	function handle(\Base $f3, $args) {

		if (!isset($args['type']))
			$this->fw->error(400,'no type was defined');

		if (!isset($args['action']))
			$this->fw->error(400,'no action was defined');

		$method = $this->fw->camelcase(str_replace('-','_',$args['action']));

		if (!isset($this->types[$args['type']]))
			$this->fw->error(400,'no handler found for that type');

		$comp = $this->types[$args['type']];

		Factory::instance()->call($comp.'.'.$method,[$this->fw,$args],'beforeroute,afterroute');
	}

	/**
	 * @param \Base $f3
	 * @param $args
	 */
	public function login(\Base $f3, $args) {
		if ($f3->exists('POST.username',$user) && $f3->exists('POST.password',$pw)) {
			$this->backendAuthService->on('load',function($username,$auth) {
				$backendUser = new BackendUser();
				$backendUser->load(['username = ?',$username]);
				if ($backendUser->valid())
					$auth->setModel($backendUser);
			});
			if ($this->backendAuthService->checkLogin($user,$pw)) {
				$user = $this->backendAuthService->getModel();
				$user_data = $user->cast();
				$user_data = array_intersect_key($user_data, array_flip(['username','name','surname','email','role','_id']));
				$jwt_service = $this->backendAuthService->service();
				$jwt_service->loginAs($user->username,['user'=>$user_data]);
				$token = $jwt_service->getAuthToken();
				$view = new JSON();
				$view->set('token',$token);
				$view->dump();
			}
		}
		return false;
	}

	/**
	 * build auth header
	 * @return string
	 */
	protected function getAuthHeader() {
		if ($this->auth_header)
			return $this->auth_header;

		if ($this->authService->isAuthenticated()) {
			$this->auth_header = 'Authorization: Bearer '.$this->authService->getJWT();
			return $this->auth_header;
		} else {
			$this->fw->error(403);
		}
	}

	/**
	 * do GET request
	 * @param $uri
	 * @return bool|mixed
	 */
	function get($uri) {
		$options = ['header' => [$this->getAuthHeader()]];
		$out = \Web::instance()->request('config-api/'.$uri,$options);
		if (isset($out['body'])) {
			return json_decode($out['body'],true);
		}
		return false;
	}

	/**
	 * do POST request
	 * @param $uri
	 * @param $data
	 * @return bool|mixed
	 */
	function post($uri,$data) {
		$options = [
			'method'  => 'POST',
			'content' => http_build_query($data),
			'header' => [$this->getAuthHeader()]
		];
		$out = \Web::instance()->request('config-api/'.$uri,$options);
		if (isset($out['body'])) {
			return json_decode($out['body'],true);
		}
		return false;
	}

	/**
	 * do DELETE request
	 * @param $uri
	 * @return bool|mixed
	 */
	function delete($uri) {
		$options = [
			'method'  => 'DELETE',
			'header' => [$this->getAuthHeader()]
		];
		$out = \Web::instance()->request('config-api/'.$uri,$options);
		if (isset($out['body'])) {
			return json_decode($out['body'],true);
		}
		return false;
	}

}