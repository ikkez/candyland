<?php

namespace Sugar\Auth;

use DB\Cortex;
use Sugar\Auth\Service\AuthServiceInterface;
use Sugar\Component;

class SimpleAuth extends Component {

	protected $model;

	/** @var AuthServiceInterface */
	protected $auth_service;

	/** @var array additional arguments for storage/mapper */
	protected $storage_args;

	/** @var string, the type of hashing strategy used to compare the password */
	protected $hashing_strategy;

	function __construct(AuthServiceInterface $authService, \Sugar\Model\Base $model) {
		$this->auth_service = $authService;
		$this->model = $model;
	}

	/**
	 * check if a user is logged in
	 * @return bool
	 */
	function isLoggedIn() {
		return $this->auth_service->isAuthenticated();
	}

	/**
	 * get active user if any
	 * @return bool|object
	 */
	function getUser() {
		if ($this->auth_service->isAuthenticated()) {
			if ($this->model->dry())
				$this->emit('load',$this->auth_service->getAuthValue(),$this);
			return $this->model;
		} else
		return false;
	}

	/**
	 * set user model
	 * @param $model
	 */
	function setModel($model) {
		$this->model = $model;
	}

	/**
	 * log in as a specific user on the current session
	 * @param $id
	 * @param null $model
	 */
	function logInAsUser($id,$model=null) {
		$this->auth_service->loginAs($id);
		if ($model)
			$this->model = $model;
		else {
			$this->emit('load',$id,$this);
		}
	}

	/**
	 * process login request
	 * @param $id
	 * @param $password
	 * @param array $args
	 * @return bool
	 */
	function login($id,$password,$args=NULL) {

		$success = $this->checkLogin($id,$password,$args);

		$this->emit('login',['success'=>$success,'id'=>$id]);

		if ($success) {
			$this->logInAsUser($id);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * check if login credentials are correct
	 * @param $id
	 * @param $password
	 * @param null $args
	 * @return bool
	 */
	function checkLogin($id,$password,$args=NULL) {
		$func = NULL;
		if (is_a($this->model,'DB\Cursor'))
			switch ($this->hashing_strategy) {
				case 'password_hash':
					$func = function($pw,$hash) {
						return password_verify($pw,$hash);
					};
					break;
				case 'bcrypt':
					$func = function($pw,$hash) {
						return \Bcrypt::instance()->verify($pw,$hash);
					};
					break;
				case 'sha':
					$password=sha1($password);
					break;
				case 'md5':
					$password=md5($password);
					break;
			}

		$auth = new \Auth($this->model, $this->storage_args,$func);

		// treat Cortex Mappers as SQL storage
		if ($this->model instanceof Cortex) {
			$refl=new \ReflectionObject($auth);
			$storage_prop=$refl->getProperty('storage');
			$storage_prop->setAccessible(true);
			$storage_prop->setValue($auth,'sql');
			$storage_prop->setAccessible(false);
		}

		return $auth->login($id,$password,$args);
	}

	/**
	 * remove authentication flag
	 */
	function logout() {
		$user = $this->getUser();
		$this->emit('beforelogout',$user,$this);
		$this->auth_service->logout();
		$this->emit('afterlogout',$user,$this);
	}


}