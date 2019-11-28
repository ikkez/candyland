<?php

namespace Sugar\Log\Clockwork;

use DB\SQL;
use Sugar\Component;
use Sugar\Storage;

class Debugger extends Component {

	function ready() {

//		if (!$this->fw->exists('DEV',$dev) || !$dev)
//			return;

		\Clockwork\Support\Vanilla\Clockwork::init([
			'register_helpers'     => true,
			'enable'               => (bool) $this->fw->get('DEBUG'),
			'storage'              => 'files',
			'storage_files_path'   => $this->fw->get('LOGS').'clockwork/',
			//'storage'              => 'sql',
//			'storage_sql_database' => 'sqlite:data/clockwork.sqlite'
		]);

		$this->fw->set('QUIET', TRUE);
		clock()->startEvent('init', "App Init");

		$this->_parent->on('beforeLoad',function(){
			clock()->endEvent('init');

			clock()->startEvent('app_load', "App Load");
		});
		$this->_parent->on('beforeRun',function(){
			clock()->endEvent('app_load');
			clock()->startEvent('app', "App Run");
		});

		$this->ev->on('route.call',function($args,$context,$ev){
			clock()->startEvent('route.call', $args);
		});

		$this->ev->on('component.load',function($args,$context,$ev){
			clock()->startEvent('component.load.'.spl_object_hash($context), 'load: '.$args['name']);
		});

		$this->ev->on('component.ready',function($args,$context,$ev){
			clock()->endEvent('component.load.'.spl_object_hash($context));
			clock()->info('component.ready',$args);
		});

		$this->ev->on('component.port.open',function($args,$context,$ev){
			clock()->startEvent('component.'.spl_object_hash($context).'.port.'.$args['port'], 'port: '.$args['name'].".".$args['port']);
		});

		$this->ev->on('component.port.close',function($args,$context,$ev){
			clock()->endEvent('component.'.spl_object_hash($context).'.port.'.$args['port']);
		});

		$this->_parent->on('afterRun',function(){
			/** @var \Base $f3 */
			$f3 = \Base::instance();
			$obj = Storage::instance()->get('sql');
			if ($obj instanceof SQL) {
				$logs = $obj->log();
				$logs = explode("\n",$logs);
				foreach ($logs as $line) {
					$ex = explode(')',$line,2);
					if (preg_match('/\((\d+\.\d+ms)\)/',$ex[0].')',$match)) {
						$time = ((float) $match[1]);
						$query = trim($ex[1]);
						clock()->addDatabaseQuery($query, [], $time);
					}
				}
			}

			clock()->requestProcessed();
			echo $f3->RESPONSE;
		});

		$this->fw->route('GET /__clockwork/*', function ($f3, $args) {
			return clock()->returnMetadata( $args['*'] );
		});

	}

}