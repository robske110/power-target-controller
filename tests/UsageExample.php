<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\tests;

use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerBudget;
use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerLowerLimit;
use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerRequirement;
use robske_110\energymanagement\powertargetcontroller\calculator\adjustment\PowerUpperLimit;
use robske_110\energymanagement\powertargetcontroller\calculator\PowerTargetCalculator;
use robske_110\energymanagement\powertargetcontroller\provider\PowerProvider;
use robske_110\energymanagement\powertargetcontroller\provider\PowerProviderMode;
use robske_110\energymanagement\powertargetcontroller\provider\PowerStep;
use robske_110\energymanagement\powertargetcontroller\tracker\PowerTargetTracker;

class WBPowerStep implements PowerStep{
	public function __construct(private int $current){}

	public function getPowerValue(): float{
		return $this->current;
	}
}

class SinglePhaseMode extends PowerProviderMode{
	public function getPossiblePowerSteps(): array{
		return [
			new WBPowerStep(230*6),
			new WBPowerStep(230*10),
			new WBPowerStep(230*16)
		];
	}
}

class ThreePhaseMode extends PowerProviderMode{
	public function getPossiblePowerSteps(): array{
		return [
			new WBPowerStep(230*3*6),
			new WBPowerStep(230*3*10),
			new WBPowerStep(230*3*16)
		];
	}
}

class Wallbox extends PowerProvider{
	public function getCurrentlyPossibleModes(): array{
		return [
			new SinglePhaseMode(),
			new ThreePhaseMode()
		];
	}
}

class PLL implements PowerLowerLimit{
	public function __construct(private float $value){
	}

	public function getCurrentAdjustmentValue(): float{
		return $this->value;
	}
}

class PUL implements PowerUpperLimit{
	public function __construct(private float $value){
	}

	public function getCurrentAdjustmentValue(): float{
		return $this->value;
	}
}

class PB implements PowerBudget{
	public function __construct(private float $value){
	}

	public function getCurrentAdjustmentValue(): float{
		return $this->value;
	}
}

class PR implements PowerRequirement{
	public function __construct(private float $value){
	}

	public function getCurrentAdjustmentValue(): float{
		return $this->value;
	}
}

$tracker = new PowerTargetTracker(new Wallbox());

$tracker->trackTarget(11000);

$calculator = new PowerTargetCalculator();

$calculator->addTargetAdjustment(new PB(10));
$calculator->addTargetAdjustment(new PR(9));
$calculator->addTargetAdjustment(new PLL(10));
$calculator->addTargetAdjustment(new PUL(9));

var_dump($calculator->getCurrentPowerTarget());
var_dump($calculator->explainPowerTarget());