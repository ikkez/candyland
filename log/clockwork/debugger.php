<?php

namespace Sugar\Log\Clockwork;

use Clockwork\Authentication\SimpleAuthenticator;
use DB\SQL;
use Sugar\Component;
use Sugar\ComponentTrait;
use Sugar\Storage;

class Debugger extends \Prefab {

	use ComponentTrait;

	function ready() {

		if ($this->fw->exists('ENABLE_CLOCKWORK',$go) && !$go)
			return;

		$options = [
			'register_helpers'     => true,
			'enable'               => true,
			'storage'              => 'files',
			'storage_files_path'   => $this->fw->get('LOGS').'clockwork/',
			//'storage'              => 'sql',
			//			'storage_sql_database' => 'sqlite:data/clockwork.sqlite',
			'storage_expiration' => 60*24*7, // minutes
			'serialization_depth' => 3,
			'auth_password' => '',
		];
		$options = array_replace_recursive($options,$this->config);
		$clock = \Clockwork\Support\Vanilla\Clockwork::init($options);

		if (!empty($options['auth_password'])) {
			$auth = new SimpleAuthenticator($options['auth_password']);
			clock()->setAuthenticator($auth);
		}

		// authentication route
		$this->fw->route('GET|POST /__clockwork/auth', function ($f3, $args) {
			$token = clock()->getAuthenticator()->attempt(['password'=>$f3->get('POST.password')]);
			header('Content-Type: application/json');
			$f3->status($token ? 200 : 403);
			echo json_encode(['token'=>$token]);
			exit();
		});

		if ($this->fw->exists('HEADERS.X-Clockwork-Auth',$token)) {

			$authenticated = clock()->getAuthenticator()->check($token);
			if ($authenticated === true) {
				// skip tracking requests from browser plugin itself so only
				// register route for receiving meta data
				$this->fw->route('GET /__clockwork/*', function ($f3, $args) {
					return clock()->returnMetadata( $args['*'] );
				});

			} else {
				header('Content-Type: application/json');
				$this->fw->status(403);
				echo json_encode(['message'=>$authenticated,'requires' => clock()->getAuthenticator()->requires()]);
				exit();
			}

		} else {

			$this->fw->set('QUIET',TRUE);
			clock()->startEvent('init',"App Init");

			$this->_parent->on('beforeLoad',function() {
				clock()->endEvent('init');

				clock()->startEvent('app_load',"App Load");
			});
			$this->_parent->on('beforeRun',function() {
				clock()->endEvent('app_load');
				$this->logHive();
				clock()->startEvent('app',"App Run");
			});

			$this->ev->on('route.call',function($args,$context,$ev) {
				clock()->startEvent('route.call',$args);
			});

			$this->ev->on('component.load',function($args,$context,$ev) {
				clock()->startEvent('component.load.'.spl_object_hash($context),
					'load: '.$args['name']);
			});

			$this->ev->on('component.ready',function($args,$context,$ev) {
				clock()->endEvent('component.load.'.spl_object_hash($context));
				clock()->info('component.ready: '.$args['name'],$args);
			});

			$this->ev->on('component.port.open',function($args,$context,$ev) {
				clock()->startEvent('component.'.spl_object_hash($context).'.port.'.
					$args['port'],'port: '.$args['name'].".".$args['port']);
			});

			$this->ev->on('component.port.close',function($args,$context,$ev) {
				clock()->endEvent('component.'.spl_object_hash($context).'.port.'.
					$args['port']);
			});

			$this->_parent->on('afterRun',function() {
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
							clock()->addDatabaseQuery($query,[],$time);
						}
					}
				}

				clock()->requestProcessed();
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

		clock()->info('HIVE',\Base::instance()->hive());
	}

	function logContext($args,$context,$ev) {
		if (!is_array($context))
			$context = [$context];
		clock()->info($ev['key'],$context);
	}

	function logArgs($args,$context,$ev) {
		clock()->info($ev['key'],$args);
	}

}