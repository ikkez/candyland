<?php

namespace Sugar\Log\Clockwork;

use Clockwork\Authentication\SimpleAuthenticator;
use DB\SQL;
use Sugar\ComponentTrait;
use Sugar\Storage;

class Debugger extends \Prefab {

	use ComponentTrait;

	protected $enabled = false;
	protected $clock;

	function ready() {

		if (!isset($this->config['enable']) || !$this->config['enable'])
			return;

		$options = [
			'api'                  => $this->fw->BASE.'/__clockwork/',
			'register_helpers'     => true,
			'enable'               => true,
			'check_cookie'         => 'XDEBUG_SESSION',
			'storage'              => 'files', // sql
			'storage_files_path'   => $this->fw->get('LOGS').'clockwork/',
			'storage_sql_database' => 'sqlite:data/clockwork.sqlite',
			'storage_expiration'   => 60*24*7, // minutes
			'serialization_depth'  => 3,
			'auth_password'        => '',
		];
		$options = array_replace_recursive($options,$this->config);

		if (!empty($options['check_cookie']) && !$this->fw->exists('COOKIE.'.$options['check_cookie'])) {
			return;
		}

		$this->enabled = TRUE;

		$clock = \Clockwork\Support\Vanilla\Clockwork::init($options);
		$this->clock = $clock;

		// create authenticator when a password was defined
		if (!empty($options['auth_password'])) {
			$auth = new SimpleAuthenticator($options['auth_password']);
			$clock->setAuthenticator($auth);
		}

		// validate auth token and produce response with instructions if unauthenticated
		if ($this->fw->exists('HEADERS.X-Clockwork-Auth',$token)) {
			$authenticated = $clock->getAuthenticator()->check($token);
			if (!$authenticated) {
				header('Content-Type: application/json');
				$this->fw->status(403);
				echo json_encode(['message'=>$authenticated,'requires' => $clock->getAuthenticator()->requires()]);
				exit();
			}
		}

		if (preg_match('/^\/__clockwork\/.*/i',$this->fw->PATH)) {

			// authentication route
			$this->fw->route('GET|POST /__clockwork/auth', function ($f3, $args) use ($clock) {
				$token = $clock->getAuthenticator()->attempt(['password'=>$f3->get('POST.password')]);
				header('Content-Type: application/json');
				$f3->status($token ? 200 : 403);
				echo json_encode(['token'=>$token]);
				exit();
			});

			// metadata request from browser plugin
			$this->fw->route('GET /__clockwork/*', function ($f3, $args) use ($clock)  {
				return $clock->returnMetadata( $args['*'] );
			});

		} else {
			// setup tracking requests

			$this->fw->set('QUIET',TRUE);
			$clock->startEvent('init',"App Init");

			$this->_parent->on('beforeLoad',function() use ($clock)  {
				$clock->endEvent('init');

				$clock->startEvent('app_load',"App Load");
			});
			$this->_parent->on('beforeRun',function() use ($clock)  {
				$clock->endEvent('app_load');
				$this->logHive();
				$clock->startEvent('app',"App Run");
			});

			$this->ev->on('route.call',function($args,$context,$ev) use ($clock)  {
				$clock->startEvent('route.call',$args);
			});

			$this->ev->on('component_load',function($args,$context,$ev) use ($clock)  {
				$clock->startEvent('component.load.'.spl_object_hash($context),
					'load: '.$args['name']);
			});

			$this->ev->on('component_ready',function($args,$context,$ev) use ($clock)  {
				$clock->endEvent('component.load.'.spl_object_hash($context));
				$clock->info('component.ready: '.$args['name'],$args);
			});

			$this->ev->on('component_port_open',function($args,$context,$ev) use ($clock)  {
				$clock->startEvent('component.'.spl_object_hash($context).'.port.'.
					$args['port'],'port: '.$args['name'].".".$args['port']);
			});

			$this->ev->on('component_port_close',function($args,$context,$ev) use ($clock)  {
				$clock->endEvent('component.'.spl_object_hash($context).'.port.'.
					$args['port']);
			});

			$this->_parent->on('afterRun',function() use ($clock)  {
				/** @var \Base $f3 */
				$f3=\Base::instance();
				$obj=Storage::instance()->get('sql');
				if ($obj instanceof SQL) {
					$logs=$obj->log();
					$logs=explode("\n",$logs);
					foreach ($logs as $line) {
						$ex=explode(')',$line,2);
						if (preg_match('/\((\d+\.\d+ms)\)/',$ex[0].')',$match)) {
							$time=((float)$match[1]);
							$query=trim($ex[1]);
							$clock->addDatabaseQuery($query,[],$time);
						}
					}
				}
				$clock->requestProcessed();
				echo $f3->RESPONSE;
			});
		}

	}

	function logHive() {
		//		$hive = clock()->userData('hive')->title('Hive');
		//		$out = [];
		//		foreach (\Base::instance()->hive() as  $key => $value) {
		//			$out[] = ['key' => $key, 'value' => $value];
		//		}
		//		ksort($out);
		//		$hive->table('HIVE',$out);
		if ($this->enabled)
			$this->clock->info('HIVE',\Base::instance()->hive());
	}

	function logContext($args,$context,$ev) {
		if (!is_array($context))
			$context = [$context];
		if ($this->enabled)
			$this->clock->info($ev['key'],$context);
	}

	function logArgs($args,$context,$ev) {
		if ($this->enabled)
			$this->clock->info($ev['key'],$args);
	}

}