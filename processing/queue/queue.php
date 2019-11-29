<?php

namespace Sugar\Processing\Queue;

use Sugar\Component;

class Queue extends Component {

	protected $channel='main';

	protected $data=[];

	protected $model;

	/**
	 * Queue constructor.
	 * @param JobModel $model
	 */
	function __construct(JobModel $model) {
		$this->model = $model;
	}

	/**
	 * initialize component
	 */
	function init() {
		$this->loadChannel($this->channel);
	}

	/**
	 * load jobs from given channel
	 * @param null $channel
	 */
	function loadChannel($channel=NULL) {
		if ($channel)
			$this->channel = $channel;

		$filters=[
			$this->model->enableFilter(),
			['channel = ?',$this->channel]
		];


		if (isset($this->config['retry']) && $this->config['retry'] > 0) {
			$filters[] = ['(status = ? OR status = ?) and retry < ?',
				JobModel::STATUS_IDLE,JobModel::STATUS_ERROR,$this->config['retry']];
		} else
			$filters[] = ['status = ?',JobModel::STATUS_IDLE];

		$result = $this->model->find($this->model->mergeFilter($filters));
		$this->data=[];
		foreach ($result?:[] as $item)
			$this->data[] = $item;
	}

	/**
	 * add a simple callable to queue
	 * @param string|Job $job
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
		if ($label)
			$model->label = $label;
		$model->handler = get_class($job);
		$model->job = $this->fw->serialize($job);
		$model->channel = $channel?:$this->channel;
		$model->save();
		$this->data[] = $model;
	}

	/**
	 * drop last added job
	 */
	function dequeue() {
		if ($this->data) {
			$model=array_pop($this->data);
			$model->erase();
		}
	}

	/**
	 * get all job in queue
	 * @return array
	 */
	function all() {
		return $this->data;
	}

	/**
	 * run the channel stack
	 */
	function run() {
		// extend server resources
		@ini_set('memory_limit',-1);
		@ini_set('max_execution_time',0);

		while ($jobModel = $this->dispense()) {
			$this->handle($jobModel);
			unset($jobModel);
			@gc_collect_cycles();
		}
	}

	/**
	 * return the next queued job
	 * @return bool|mixed
	 */
	function dispense() {
		if ($this->data) {
			/** @var Job $job */
			return array_shift($this->data);
		}
		return FALSE;
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
		$jobModel->save();

		$now = microtime(TRUE);
		$job = $this->fw->unserialize($jobModel->job);
		if ($job instanceof JobInterface) {
			$error_bak = $this->fw->ONERROR;
			$this->fw->HALT = false;
			$this->fw->ONERROR = function($f3,$args) use($error_bak, $jobModel) {
				$jobModel->status = JobModel::STATUS_ERROR;
				$jobModel->save();
				$this->fw->call($error_bak,[$f3,$args]);
			};
			$success = $job->exec();
			$this->fw->HALT = true;
			$this->fw->ONERROR = $error_bak;
		}
		else
			$success = false;

		// track time
		$elapsed = round(1e3*(microtime(TRUE)-$now),2);
		$jobModel->performance_ms = $elapsed;
		$jobModel->memory_mb = round(memory_get_usage(TRUE)/1e6,2);
		$jobModel->touch('exec_at');

		if ($success!==FALSE) {
			$jobModel->status = $jobModel::STATUS_DONE;
			if ($this->config['delete_completed']) {
				$jobModel->erase();
				return $success;
			}
		} else {
			$jobModel->status = $jobModel::STATUS_ERROR;
		}
		$jobModel->save();
		return $success;
	}
}