<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\tracker;

use robske_110\energymanagement\powertargetcontroller\provider\PowerProviderMode;

interface PowerStepChangeListener{
	/**
	 * @internal Called by the PowerTargetTracker while being added, should not be called from elsewhere.
	 * @param PowerTargetTracker $tracker The PowerTargetTracker where the onPowerStepChange events will originate from.
	 */
	public function setPowerTracker(PowerTargetTracker $tracker): void;

	/**
	 * @param PowerProviderMode $oldMode
	 * @param PowerProviderMode $newMode
	 *
	 * @return bool Whether to allow (true) or block this PowerStep change
	 */
	public function onPowerStepChange(PowerProviderMode $oldMode, PowerProviderMode $newMode): bool;
}