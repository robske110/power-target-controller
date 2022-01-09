<?php
declare(strict_types=1);

namespace robske_110\smartenergy\powertargetcontroller;

//TODO: dropOff functionality -> middleware for pwrStepChg
class PowerTargetTracker{
	private PowerProvider $powerProvider;

	public function __construct(PowerProvider $powerProvider){
		$this->powerProvider = $powerProvider;
	}

	public function trackTarget(float $powerTarget){
		//TODO: a ton of logic!

		//Simple nearest algorithm:
		$this->powerProvider->setMode($this->trackMinDiff($powerTarget));
	}

	private function setMode(PowerProviderMode $mode){
		$oldMode = $this->powerProvider->getMode();
		if(get_class($oldMode) === get_class($mode)){
			if($oldMode->selectedPowerStep == $mode->selectedPowerStep){
				return;
			}
			//TODO middleware for powerStepChange
		}
		//TODO middleware for modeChange!
	}

	private function trackMinDiff(float $powerTarget): PowerProviderMode{
		$minDiff = PHP_FLOAT_MAX;
		$nearestMode = null;
		foreach($this->powerProvider->getCurrentlyPossibleModes() as $powerProviderMode){
			foreach($powerProviderMode->getPossiblePowerSteps() as $possiblePowerStep){
				$currentDiff = min($minDiff, abs($powerTarget - $possiblePowerStep->getPowerValue()));
				if($currentDiff < $minDiff){
					$minDiff = $currentDiff;
					$powerProviderMode->selectedPowerStep = $possiblePowerStep;
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
}

class PowerTargetTrackerException extends \RuntimeException{

}

abstract class PowerProvider{
	/** @var PowerProviderMode */
	private PowerProviderMode $currentMode;

	/**
	 * Override this method with the actual functionality to adjust the mode and powerStep of your PowerProvider
	 * @param PowerProviderMode $mode
	 */
	public function setMode(PowerProviderMode $mode){
		var_dump($mode);
		$this->currentMode = $mode;
	}

	public function getMode(): PowerProviderMode{
		return $this->currentMode;
	}

	/**
	 * @return PowerProviderMode[]
	 */
	abstract public function getCurrentlyPossibleModes(): array;
}

abstract class PowerProviderMode{
	public ?PowerStep $selectedPowerStep;

	//TODO: possibly remove powerStep from constructor
	public function __construct(?PowerStep $powerStep = null){
		$this->selectedPowerStep = null;
	}

	//TODO: maybe check getPossiblePowerSteps on set (protected & introduce set/get)

	/**
	 * @return PowerStep[]
	 */
	abstract public function getPossiblePowerSteps(): array;
}

interface PowerStep{
	/** @return float Wattage of this powerStep */
	function getPowerValue(): float;
}

class PowerTargetCalculator{
	/** @var PowerTargetAdjustment[] */
	private array $powerTargetAdjustments;

	private array $explanation = [];

	public function addTargetAdjustment(PowerTargetAdjustment $targetAdjustment){
		$this->powerTargetAdjustments[] = $targetAdjustment;
	}

	public function removeTargetAdjustment(PowerTargetAdjustment $targetAdjustment){
		foreach($this->powerTargetAdjustments as $id => $powerTargetAdjustment){
			if($powerTargetAdjustment === $targetAdjustment){
				unset($this->powerTargetAdjustments[$id]);
			}
		}
	}

	public function getCurrentPowerTarget(): float{
		$powerTarget = 0; //CFG->powerTargetBase
		$this->explanation = [];

		$upperLimit = PHP_FLOAT_MAX;
		$lowerLimit = -PHP_FLOAT_MAX;
		foreach($this->powerTargetAdjustments as $powerTargetAdjustment){
			$adjustmentValue = $powerTargetAdjustment->getCurrentAdjustmentValue();
			if($powerTargetAdjustment instanceof PowerBudget){
				$powerTarget += $adjustmentValue;
				$this->explanation[] = [$powerTargetAdjustment::class, $adjustmentValue];
			}elseif($powerTargetAdjustment instanceof PowerRequirement){
				$powerTarget -= $adjustmentValue;
				$this->explanation[] = [$powerTargetAdjustment::class, -$adjustmentValue];
			}elseif($powerTargetAdjustment instanceof PowerLowerLimit){
				$lowerLimit = max($lowerLimit, $adjustmentValue);
			}elseif($powerTargetAdjustment instanceof PowerUpperLimit){
				$upperLimit = min($upperLimit, $adjustmentValue);
			}else{
				$powerTarget += $adjustmentValue;
				$this->explanation[] = [$powerTargetAdjustment::class, $adjustmentValue];
			}
		}
		$powerTarget = max($powerTarget, $lowerLimit);
		$powerTarget = min($powerTarget, $upperLimit);
		return $powerTarget;
	}

	public function explainPowerTarget(): array{
		return $this->explanation;
	}
}

interface PowerTargetAdjustment{
	//TYPE: POWER_BUDGET (SOFT!)
	//TYPE: POWER_REQUIREMENT
	//TYPE: POWER_LIMITER
	//TYPE: POWER_MIN
	public function getCurrentAdjustmentValue(): float;
}

interface PowerBudget extends PowerTargetAdjustment{

}

interface PowerRequirement extends PowerTargetAdjustment{

}

/** PowerUpperLimit always has higher priority than the PowerLowerLimit */
interface PowerUpperLimit extends PowerTargetAdjustment{

}

interface PowerLowerLimit extends PowerTargetAdjustment{

}