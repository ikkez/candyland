<?php

namespace Sugar\Forms\Multistep;


use Sugar\Component;
use Sugar\View\ViewInterface;

class MultistepForm extends Component {


	// run validator after submitting each step
	protected $after_step_validation = false;

	// be able to navigate beyond the current step
	protected $beyond_current_step_navitation = true;

	// always validates level 0
	protected $basic_validation = true;

	// validate all steps on final step submit
	protected $final_step_validation = true;

	// default:all steps till current; step_only:only the current step
	protected $step_validation_mode = 'default';

	// the HTTP Verb that's used to trigger validation
	protected $validate_verb = 'POST';

	// additionally validate each single step silently and mark its state in the steps array
	protected $additional_silent_validation = true;

	protected $step_route_args = [];
	protected $step_route_alias = '';
	protected $step_route_validate_alias = '';


	protected $steps = [];
	protected $current_step = 0;
	protected $final_step = 0;
	protected $validation_active = false;

	/** @var ViewInterface */
	protected $tmpl;

	/**
	 * MultistepForm constructor.
	 * @param ViewInterface $tmpl
	 */
	function setViewInterface(ViewInterface $tmpl) {
		$this->tmpl = $tmpl;
	}

	function init() {

		if (!is_array($this->steps))
			$this->steps = [];
		else
			foreach ($this->steps as &$step) {
				if (!array_key_exists('valid',$step))
					$step['valid']=NULL;
				unset($step);
			}

		if (!is_array($this->step_route_args))
			$this->step_route_args = [];

		$this->final_step = $this->getStepCount();
	}

	/**
	 * process the current step
	 */
	function run() {
		// check if out of bounds
		if (!isset($this->steps[$this->current_step]))
			$this->fw->error(404);

		// TODO: kann das weg?
		if (!$this->after_step_validation && $this->fw->ALIAS == $this->step_route_validate_alias) {
			$this->validationMode(TRUE);
		}

		// pre-validation and reroute
		if (!$this->beyond_current_step_navitation) {
			$goBack=FALSE;
			foreach ($this->steps as $num=>$step) {
				if ($num>=$this->current_step)
					break;
				if ($this->validateStep($num) == FALSE) {
					$goBack = $num;
					break;
				}
			}
			if ($goBack)
				$this->fw->reroute([$this->step_route_validate_alias,
					['step'=>$goBack]+$this->step_route_args]);
		}

		if ($this->emit('step_render',[$this->current_step],$this) !== FALSE) {
			$this->complete_step();
		}

		if ($this->tmpl) {
			$this->tmpl->set('current_step',$this->getCurrentStep());
			$this->tmpl->set('final_step',$this->getStepCount());

			$this->tmpl->set('steplink_alias',$this->step_route_alias);
			$this->tmpl->set('steplink_validate_alias',$this->step_route_validate_alias);

			$this->tmpl->set('form_action_alias',$this->validation_active ?
				$this->step_route_validate_alias : $this->step_route_alias);

			$this->tmpl->set('steps',$this->getSteps());
		}

	}

	/**
	 * validate a single step
	 * @param $num
	 * @param bool $shout
	 * @param string $op
	 * @return bool
	 */
	function validateStep($num, $shout=FALSE,$op='<=') {
		$call = 'step_validate_'.$num;
		if (method_exists($this,$call)) {
			return (bool) $this->{$call}();
		}
		else {
			$valid = true;
			$this->emit('step_validate',[
				'step'=>$num,'shout'=>$shout, 'op' => $op, 'form' => $this
			],$valid);
			return $valid;
		}
	}

	/**
	 *
	 */
	protected function complete_step() {

		if ($this->fw->ALIAS == $this->step_route_validate_alias) {
			$this->after_step_validation = true;
			$this->step_validation_mode='step_only';
			$this->validate_verb = $this->fw->VERB;
			$this->validation_active = true;
		}
		elseif ($this->final_step_validation && $this->current_step == $this->final_step) {
			$this->validation_active = true;
		}

		$saveDraftAndExit = $this->fw->exists('POST.exit');

		$valid = true;

		// validation
		if ($this->fw->VERB == $this->validate_verb) {

			$this->emit('before_validate',null,$this);

			if ($saveDraftAndExit) {
				// only validate basics and exit
				$valid = $this->validateStep(0,true);
			}
			else {
				// always validate full on final step
				if ($this->final_step_validation && $this->current_step == $this->final_step) {
					$valid = $this->validateStep($this->current_step,true);

				} elseif ($this->after_step_validation) {
					// validate from step to step
					switch ($this->step_validation_mode) {
						case 'default':
							// validate all steps till current step
							$valid = $this->validateStep($this->current_step,true);
							break;
						case 'step_only':
							// validate only the current step
							$valid = $this->validateStep($this->current_step,true,'==');
							break;
					}
				} elseif ($this->basic_validation) {
					// validate only the basics (level 0)
					$valid = $this->validateStep(0,true);
				}

			}

			$this->emit('after_validate',$valid,$this);

		}

		// silent validation
		if ($this->additional_silent_validation) {
			$steps = $this->emit('before_silent_step_validation',$this->steps,$this);
			foreach ($steps as $step_num=>&$step) {
				$step['valid']=$this->validateStep($step_num,false,'==');
				$this->steps[$step_num]['valid']=$step['valid'];
				unset($step);
			}
			$steps = $this->emit('after_silent_step_validation',$steps,$this);
		}

		$all_valid = true;
		foreach ((isset($steps)?$steps:$this->steps) as $step) {
			if (!$step['valid']) {
				$all_valid=FALSE;
				break;
			}
		}

		if ($this->tmpl) {
			$this->tmpl->set('all_valid', $all_valid);
			$this->tmpl->set('navigate_beyond_current_step',$this->beyond_current_step_navitation);
			$this->tmpl->set('validation_active',$this->validation_active);
		}

		// storage and actions
		if ($this->fw->VERB == 'POST' && $valid) {

			$this->emit('persist',null,$this);


			if ($saveDraftAndExit) {
				$this->emit('exit',null,$this);

			} else {
				if (!$this->fw->exists('POST.goto',$gotoStep))
					$gotoStep = $this->current_step+1;
					// goto next step
				if ($gotoStep <= $this->final_step)
					$this->fw->reroute([$this->validation_active ?
						$this->step_route_validate_alias : $this->step_route_alias,
						['step'=>$gotoStep]+$this->step_route_args]);
				else {
					$this->emit('complete',null,$this);
				}
			}
		}
	}


	/**
	 * @param int $val
	 */
	function setCurrentStep($val) {
		$this->current_step = $val;
	}

	/**
	 * @return int
	 */
	function getCurrentStep() {
		return $this->current_step;
	}

	/**
	 * set additional arguments being used for route alias building
	 * @param array $args
	 */
	function setRouteStepArgs(array $args) {
		$this->step_route_args = $args;
	}

	/**
	 * get route args
	 * @return array
	 */
	function getRouteStepArgs() {
		return $this->step_route_args;
	}

	/**
	 * add a step
	 * @param string $name
	 * @param array $data
	 */
	function addStep($name,$data=[]) {
		$data=['label'=>$name,'valid'=>NULL]+$data;
		$this->steps[count($this->steps)+1]=$data;
		$this->final_step = count($this->steps);
	}

	/**
	 * return steps array
	 * @return array
	 */
	function getSteps() {
		return $this->steps;
	}

	/**
	 * get amount of steps
	 * @return int
	 */
	function getStepCount() {
		return count($this->steps);
	}

	/**
	 * @param bool $state
	 * @return bool
	 */
	function validationMode($state=NULL) {
		if ($state===NULL)
			return $this->validation_active;
		else
			$this->validation_active = $state;
	}

}