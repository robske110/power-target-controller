<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\tracker;

use robske_110\energymanagement\powertargetcontroller\provider\PowerProviderMode;

interface PowerStepChangeListener{
	/**
	 * @param PowerProviderMode $oldMode
	 * @param PowerProviderMode $newMode
	 *
	 * @return bool Whether to allow (true) or block this PowerStep change
	 */
	public function onPowerStepChange(PowerProviderMode $oldMode, PowerProviderMode $newMode): bool;
}