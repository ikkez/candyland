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

	protected $components=[];

	function ready() {

		if (!isset($this->config['enable']) || !$this->config['enable'])
			return;

		$options = [
			'api'                  => $this->fw->BASE.'/__clockwork/',
			'register_helpers'     => true,
			'enable'               => true,
			'allowed_hosts'        => '',
			'check_cookie'         => 'XDEBUG_SESSION',
			'storage'              => 'files', // sql
			'storage_files_path'   => $this->fw->get('LOGS').'clockwork/',
			'storage_sql_database' => 'sqlite:data/clockwork.sqlite',
			'storage_expiration'   => 60*24*7, // minutes
			'serialization_depth'  => 3,
			'auth_password'        => '',
			'db'                   => [
				'storage_key' => 'sql'
			],
		];
		$options = array_replace_recursive($options,$this->config);

		if ($options['allowed_hosts'] && !is_array($options['allowed_hosts']))
			$options['allowed_hosts'] = [$options['allowed_hosts']];

		if ($options['allowed_hosts'] && !in_array($this->fw->HOST, $options['allowed_hosts']))
			return;

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
				$this->logAppConfig();
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
				$this->components[] = [
					'Name' => $args['name'],
					'Config' => $args,
					'Class'=>get_class($context)];
			});

			$this->ev->on('component_port_open',function($args,$context,$ev) use ($clock)  {
				$clock->startEvent('component.'.spl_object_hash($context).'.port.'.
					$args['port'],'port: '.$args['name'].".".$args['port']);
			});

			$this->ev->on('component_port_close',function($args,$context,$ev) use ($clock)  {
				$clock->endEvent('component.'.spl_object_hash($context).'.port.'.
					$args['port']);
			});

			$this->ev->on('debug',function($args,$context,$ev) use ($clock)  {
				if (!is_array($args))
					$args=[$args];
				$clock->warning('debug',$args);
			});

			$levels = [
				'emergency',
				'alert',
				'critical',
				'error',
				'warning',
				'notice',
				'info',
				'debug',
			];
			foreach ($levels as $level) {
				$this->ev->on('log.'.$level,function($args,$context,$ev) use ($clock)  {
					if (!is_array($args))
						$args=[$args];
					if (isset($args['msg'])) {
						$msg = $args['msg'];
						unset($args['msg']);
						$clock->log($ev['key'],$msg,$args);
					}
					else
						$clock->log($ev['key'],$args);
				});
			}

			$this->_parent->on('afterRun',function() use ($clock, $options)  {

				$comp = clock()->userData('components')->title('Components');
				$comps = [];
				foreach ($this->components as $item)
					$comps[$item['Name']]=true;

				$comp->counters([
					'Components' => count($comps),
					'Instances' => count($this->components),
				]);

				$comp->table('Components',$this->components);

				/** @var \Base $f3 */
				$f3=\Base::instance();
				$obj=Storage::instance()->get($options['db']['storage_key']);
				if ($obj instanceof SQL) {
					$logs=$obj->log();
					$logs=explode("\n",$logs);
					foreach ($logs as $line) {
						$ex=explode(')',$line,2);
						if (preg_match('/\((\d+(?:[.,]\d+)ms)\)/',$ex[0].')',$match)) {
							$time=((float)str_replace(',','.',$match[1]));
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
		if ($this->enabled)
			$this->clock->info('HIVE',\Base::instance()->hive());
	}

	function logAppConfig() {
		if (!$this->enabled) return;

		$app = clock()->userData('app')->title('App');
		$out = [];
		/** @var \Base $f3 */
		$f3 = \Base::instance();
		$data = $f3->get('APP');
		$data['AUTOLOAD']=$f3->AUTOLOAD;
		$data['LOCALES']=$f3->LOCALES;
		$data['TZ']=$f3->TZ;
		$data['CACHE']=$f3->CACHE;
		$data['JAR']=$f3->JAR;
		$data['BASE']=$f3->BASE;
		$data['PATH']=$f3->PATH;
		$data['ROOT']=$f3->ROOT;
		ksort($data);
		foreach ($data as $key => $value) {
			$out[] = ['key' => $key, 'value' => $value];
		}
		$app->table('App',$out);

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