<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\calculator;

use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerBudget;
use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerLowerLimit;
use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerRequirement;
use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerUpperLimit;

class PowerTargetCalculator{
	/** @var PowerTargetAdjustment[] */
	private array $powerTargetAdjustments;

	private array $explanation = [];

	public function addTargetAdjustment(PowerTargetAdjustment $targetAdjustment){
		$this->powerTargetAdjustments[] = $targetAdjustment;
	}

	public function removeTargetAdjustment(PowerTargetAdjustment $targetAdjustment){
		foreach ($this->powerTargetAdjustments as $id => $powerTargetAdjustment){
			if ($powerTargetAdjustment === $targetAdjustment){
				unset($this->powerTargetAdjustments[$id]);
			}
		}
	}

	public function getCurrentPowerTarget(): float{
		$powerTarget = 0; //CFG->powerTargetBase
		$this->explanation = [];

		$upperLimit = PHP_FLOAT_MAX;
		$lowerLimit = -PHP_FLOAT_MAX;
		foreach ($this->powerTargetAdjustments as $powerTargetAdjustment){
			$adjustmentValue = $powerTargetAdjustment->getCurrentAdjustmentValue();
			if ($powerTargetAdjustment instanceof PowerBudget){
				$powerTarget += $adjustmentValue;
				$this->explanation[] = [$powerTargetAdjustment::class, $adjustmentValue];
			}elseif ($powerTargetAdjustment instanceof PowerRequirement){
				$powerTarget -= $adjustmentValue;
				$this->explanation[] = [$powerTargetAdjustment::class, -$adjustmentValue];
			}elseif ($powerTargetAdjustment instanceof PowerLowerLimit){
				$lowerLimit = max($lowerLimit, $adjustmentValue);
			}elseif ($powerTargetAdjustment instanceof PowerUpperLimit){
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