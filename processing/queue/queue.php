<?php

namespace Sugar\Processing\Queue;

use Service\SysLog;
use Sugar\Component;

class Queue extends Component {

	protected string $channel='main';

	protected int $i=0;

	protected JobModel $model;

	/**
	 * Queue constructor.
	 * @param JobModel $model
	 */
	function __construct(JobModel $model) {
		$this->model = $model;
	}

	/**
	 * add a simple callable to queue
	 * @param string|JobInterface $job
	 * @param null $args
	 * @param null $label
	 * @param null $channel
	 */
	function enqueue($job,$args=NULL,$label=NULL,$channel=NULL) {
		if (!(is_string($job) || $job instanceof JobInterface))
			$this->fw->error(500,'Only callable strings and Job instances are allowed');
		if (!($job instanceof JobInterface)) {
			/** @var Job $job */
			$job = new Job($job,$args);
		}
		$this->addJob($job,$label,$channel);
	}

	/**
	 * add and persist new job
	 * @param JobInterface $job
	 * @param null $label
	 * @param null $channel
	 */
	function addJob(JobInterface $job,$label=NULL,$channel=NULL) {
		/** @var JobModel $model */
		$model = new $this->model;
		$model->defaults(true);
		if ($label)
			$model->label = $label;
		$model->handler = get_class($job);
		$model->job = $this->fw->serialize($job);
		$model->channel = $channel?:$this->channel;
		$model->save();
	}

	/**
	 * register new worker according to settings
	 * @return FALSE|string
	 */
	protected function registerWorker() {
		return $this->fw->mutex('register_queue_worker', function() {
			if ($this->fw->CACHE && ($cache = \Cache::instance())) {
				if ($this->config['max_workers'] && count(self::getWorker($this->channel)) >= $this->config['max_workers'])
					return FALSE;
				$workerId = 'worker_'.$this->channel.'_'.$this->fw->hash(uniqid($this->fw->SEED,true));
				$cache->set($workerId,[
					'started_at' => time(),
					'jobs' => 0,
					'heartbeat' => time(),
					'channel' => $this->channel,
					'state' => 'idle',
				]);
				$workers = self::getWorker();
				$workers[] = $workerId;
				$cache->set('queue_worker', $workers);
				return $workerId;
			}
			return FALSE;
		});
	}

	/**
	 * remove worker id from cache
	 * @param $workerId
	 */
	static protected function unregisterWorker($workerId) {
		/** @var \Base $f3 */
		$f3 = \Base::instance();
		if ($f3->CACHE && ($cache = \Cache::instance()) && $cache->exists('queue_worker')) {
			$workers = $cache->get('queue_worker');
			foreach ($workers as $key => $item) {
				if ($item === $workerId)
					unset($workers[$key]);
			}
			$cache->set('queue_worker', $workers);
			$cache->clear($workerId);
			SysLog::notice('Worker shutdown: '.$workerId);
		}
	}

	/**
	 * get existing worker ids
	 * @param string|null $filterChannel
	 * @return array
	 */
	static public function getWorker(string $filterChannel=NULL): array {
		$cache = \Cache::instance();
		$workers = [];
		if ($cache->exists('queue_worker'))
			$workers = $cache->get('queue_worker');
		if ($filterChannel)
			foreach ($workers as $key => $item) {
				if (strpos($item,$filterChannel) === FALSE)
					unset($workers[$key]);
			}
		return $workers;
	}

	/**
	 * get status for running workers
	 * @return array
	 */
	static public function getWorkerDetails(): array {
		$workers = self::getWorker();
		$out = [];
		$cache = \Cache::instance();
		foreach ($workers as $workerId) {
			if ($cache->exists($workerId, $data))
				$out[$workerId] = $data;
			else
				$out[$workerId] = null;
		}
		return $out;
	}

	/**
	 * remove worker entries from cache storage that had no recent heartbeat
	 */
	static public function cleanUpWorker() {
		$workers = self::getWorkerDetails();
		foreach ($workers as $id => $data) {
			if (empty($data) || (isset($data['heartbeat']) && $data['heartbeat'] < time() - (90))) {
				SysLog::notice('cleanup: '.$id);
				self::unregisterWorker($id);
			}
		}
	}

	/**
	 * run the channel stack
	 */
	function run() {
		// extend server resources
		@ini_set('memory_limit',-1);
		@ini_set('max_execution_time',0);

		self::cleanUpWorker();
		$workerId = $this->registerWorker();
		if (!$workerId)
			return;
		$cache = \Cache::instance();
		$worker = $cache->get($workerId);
		if (!$worker)
			return;
		SysLog::notice('started: '.$workerId);
		try {
			while (!$this->config['max_lifetime']
				|| strtotime($this->config['max_lifetime'],$worker['started_at']) > time()) {
				// idle loop
				sleep(rand(1,10));
				while ($jobModel = $this->dispense()) {
					// work loop
					$workerData = $cache->get($workerId);
					if ($workerData) {
						$workerData['heartbeat']=time();
						$workerData['state']='working';
						$workerData['active_job']=$jobModel->id;
						$cache->set($workerId,$workerData);
					}
					$this->handle($jobModel);
					unset($jobModel);
					@gc_collect_cycles();
					// update worker stats in cache
					$workerData = $cache->get($workerId);
					if (!$workerData)
						break;
					$workerData['jobs']++;
					$workerData['heartbeat']=time();
					$cache->set($workerId,$workerData);
				}
				$workerData = $cache->get($workerId);
				if ($workerData) {
					$workerData['heartbeat']=time();
					$workerData['state']='idle';
					$workerData['active_job']=null;
					$cache->set($workerId,$workerData);
				} else self::unregisterWorker($workerId);
			}
		} finally {
			SysLog::notice('restarting: '.$workerId);
			self::unregisterWorker($workerId);
		}
	}

	/**
	 * return the next queued job
	 */
	function dispense(): ?JobModel {
		$filters=[
			$this->model->enableFilter(),
			['channel = ?',$this->channel]
		];

		if (isset($this->config['retry']) && $this->config['retry'] > 0) {
			$filters[] = ['(status = ? OR status = ?) and retry < ?',
				JobModel::STATUS_IDLE,JobModel::STATUS_ERROR,$this->config['retry']];
		} else
			$filters[] = ['status = ?',JobModel::STATUS_IDLE];

		if (isset($this->config['limit']) && $this->config['limit'] > 0) {
			if ($this->i === $this->config['limit'])
				return NULL;
			$this->i++;
		}

		$options = [];
		if (isset($this->config['reverse']) && $this->config['reverse'] === TRUE) {
			$options['order'] = 'id desc';
		}

		// reset SQL log
		$db = $this->model->dbEngine();
		$ref = new \ReflectionClass($db);
		$ref_prop = $ref->getProperty('log');
		$ref_prop->setAccessible(TRUE);
		$ref_prop->setValue($db,'');
		$job = $this->fw->mutex('get_next_job', fn() => $this->model->findone($this->model->mergeFilter($filters), $options ?: NULL));
		return $job ?: NULL;
	}

	/**
	 * @param JobModel $jobModel
	 * @return bool|FALSE|mixed
	 */
	function handle(JobModel $jobModel) {
		$jobModel->run++;
		if ($jobModel->status == JobModel::STATUS_ERROR) {
			$jobModel->retry++;
		}
		$jobModel->status = JobModel::STATUS_ACTIVE;
		$jobModel->touch('updated_at');
		$jobModel->save();

		$now = microtime(TRUE);
		/** @var Job $job */
		$job = $this->fw->unserialize($jobModel->job);
		if ($job instanceof JobInterface) {
            $error_bak = $this->fw->ONERROR;
            $this->fw->HALT = false;
            $this->fw->ONERROR = function($f3,$args) use($error_bak, $jobModel) {
                $jobModel->status = JobModel::STATUS_ERROR;
                $jobModel->touch('updated_at');
                $jobModel->save();
                $this->fw->call($error_bak,[$f3,$args]);
            };
			$result = $job->exec();
            $this->fw->HALT = true;
			$success = $jobModel->status == JobModel::STATUS_ERROR ? FALSE : $result;
			$this->fw->ONERROR = $error_bak;
		}
		else
			$success = FALSE;

		// track time
		$elapsed = round(1e3*(microtime(TRUE)-$now),2);
		$jobModel->performance_ms = $elapsed;
		$jobModel->memory_mb = round(memory_get_usage(TRUE)/1e6,2);
		$jobModel->touch('exec_at');

		if ($success!==FALSE) {
			$jobModel->status = JobModel::STATUS_DONE;
			if ($this->config['delete_completed']) {
				$jobModel->erase();
				return $success;
			}
		} else {
			$jobModel->status = JobModel::STATUS_ERROR;
		}
		$jobModel->touch('updated_at');
		$jobModel->save();
        SysLog::info(sprintf('Job handled: %s (%.3F s)',$jobModel->handler, $elapsed / 1000));
        return $success;
	}
}
