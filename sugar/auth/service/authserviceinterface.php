<?php

namespace Sugar\Auth\Service;


interface AuthServiceInterface {

	/**
	 * check if a user is logged in
	 * @return bool
	 */
	function isAuthenticated();

	/**
	 * get authentication value, user or entity data
	 * @return mixed
	 */
	function getAuthValue();

	/**
	 * authenticated as a specific user/entity
	 * @param $auth_value
	 */
	function loginAs($auth_value);

	/**
	 * remove authentication flag
	 */
	function logout();
}