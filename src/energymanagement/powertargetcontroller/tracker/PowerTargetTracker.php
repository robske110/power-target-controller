<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\tracker;

use robske_110\energymanagement\powertargetcontroller\provider\PowerProvider;
use robske_110\energymanagement\powertargetcontroller\provider\PowerProviderMode;

class PowerTargetTracker{
	private PowerProvider $powerProvider;
	private PowerTargetTrackerOptions $options;

	/** @var PowerStepChangeListener[] */
	private array $powerStepChangeListeners = [];
	/** @var PowerProviderModeChangeListener[] */
	private array $powerProviderModeChangeListeners = [];

	public function __construct(PowerProvider $powerProvider, PowerTargetTrackerOptions $options){
		$this->powerProvider = $powerProvider;
		$this->options = $options;
	}

	public function trackTarget(float $powerTarget){
		$this->setMode(match($this->options->get("mode.selected")){
			"minDiff" => $this->trackMinDiff($powerTarget),
			"keepBelow" => $this->trackBelow($powerTarget),
			"keepAbove" => $this->trackAbove($powerTarget)
		});
	}

	public function addPowerStepChangeListener(PowerStepChangeListener $powerStepChangeListener){
		$this->powerStepChangeListeners[] = $powerStepChangeListener;
	}

	public function addPowerProviderModeChangeListener(PowerProviderModeChangeListener $modeChangeListener){
		$this->powerProviderModeChangeListeners[] = $modeChangeListener;
	}

	private function setMode(PowerProviderMode $mode){
		$oldMode = $this->powerProvider->getMode();
		if(get_class($oldMode) === get_class($mode)){
			if($oldMode->getSelectedPowerStep() == $mode->getSelectedPowerStep()){
				return;
			}
			foreach($this->powerStepChangeListeners as $powerStepChangeListener){
				if(!$powerStepChangeListener->onPowerStepChange($oldMode, $mode)){
					return;
				}
			}
		}else{
			foreach($this->powerProviderModeChangeListeners as $modeChangeListener){
				if(!$modeChangeListener->onPowerModeChange($oldMode, $mode)){
					return;
				}
			}
		}
		$this->powerProvider->setMode($mode);
	}

	private function trackMinDiff(float $powerTarget): PowerProviderMode{
		$minDiff = PHP_FLOAT_MAX;
		$nearestMode = null;
		foreach($this->powerProvider->getCurrentlyPossibleModes() as $powerProviderMode){
			foreach($powerProviderMode->getPossiblePowerSteps() as $possiblePowerStep){
				$currentDiff = min($minDiff, abs($powerTarget - $possiblePowerStep->getPowerValue()));
				if($currentDiff < $minDiff){
					$minDiff = $currentDiff;
					$powerProviderMode->setSelectedPowerStep($possiblePowerStep);
					$nearestMode = $powerProviderMode;
				}
			}
		}
		if($nearestMode === null){
			throw new PowerTargetTrackerException(
				"PowerTargetTracker failed to track powerTarget: PowerProvider returned no possible powerSteps!"
			);
		}
		return $nearestMode;
	}

	/**
	 * TrackBelow algorithm
	 * Selects the closest PowerProviderMode to the powerTarget that is lower than powerTarget+mode.keepBelow.allowAbove.
	 * If there is no PowerProviderMode lower than powerTarget+mode.keepBelow.allowAbove, the closest PowerProviderMode
	 * to the powerTarget is returned.
	 * If two PowerProviderModes have the same power value, this implementation will select the last one returned.
	 * @param float $powerTarget
	 *
	 * @return PowerProviderMode
	 */
	private function trackBelow(float $powerTarget): PowerProviderMode{
		return $this->trackMinDiffFiltered($powerTarget, function(float $relDiff): bool{
			return $relDiff <= $this->options->get("mode.keepBelow.allowAbove");
		});
	}

	/**
	 * TrackBelow algorithm
	 * Selects the closest PowerProviderMode to the powerTarget that is lower than powerTarget-mode.keepAbove.allowBelow.
	 * If there is no PowerProviderMode larger than powerTarget-mode.keepAbove.allowBelow, the closest PowerProviderMode
	 * to the powerTarget is returned.
	 * If two PowerProviderModes have the same power value, this implementation will select the last one returned.
	 * @param float $powerTarget
	 *
	 * @return PowerProviderMode
	 */
	private function trackAbove(float $powerTarget): PowerProviderMode{
		return $this->trackMinDiffFiltered($powerTarget, function(float $relDiff): bool{
			return $relDiff >= -$this->options->get("mode.keepAbove.allowBelow");
		});
	}

	/**
	 * TrackMinDiffFiltered algorithm
	 * Selects the closest PowerProviderMode to the powerTarget that fulfills the given $filter.
	 * If there is no PowerProviderMode that fulfills the $filter, the closest PowerProviderMode to the powerTarget is
	 * returned.
	 * If two PowerProviderModes have the same power value, this implementation will select the last one returned.
	 * @param float $powerTarget
	 * @param callable $filter Filter for relativeDifference. Signature: $filter(float $relDiff): bool
	 *
	 * @return PowerProviderMode
	 */
	private function trackMinDiffFiltered(float $powerTarget, callable $filter): PowerProviderMode{
		$relDiffToMode = [];
		foreach($this->powerProvider->getCurrentlyPossibleModes() as $powerProviderMode){
			foreach($powerProviderMode->getPossiblePowerSteps() as $possiblePowerStep){
				$relDiffToMode[$possiblePowerStep->getPowerValue() - $powerTarget] = [$powerProviderMode, $possiblePowerStep];
			}
		}
		$relDiffs = array_keys($relDiffToMode);
		$minDiff = PHP_FLOAT_MAX;
		$mode = null;
		foreach($relDiffs as $relDiff){
			if(abs($relDiff) < $minDiff && $filter($relDiff)){
				$minDiff = abs($relDiff);
				$mode = $relDiffToMode[$relDiff];
			}
		}
		$minDiff = PHP_FLOAT_MAX;
		if($mode === null){ //could not find any powerModes that the filter accepts, fallback to selecting the closest
			foreach($relDiffs as $relDiff){
				if(abs($relDiff) < $minDiff){
					$mode = $relDiffToMode[$relDiff];
				}
			}
		}
		if($mode === null){
			throw new PowerTargetTrackerException(
				"PowerTargetTracker failed to track powerTarget: PowerProvider returned no possible powerSteps!"
			);
		}
		$mode[0]->setSelectedPowerStep($mode[1]);
		return $mode[0];
	}
}
