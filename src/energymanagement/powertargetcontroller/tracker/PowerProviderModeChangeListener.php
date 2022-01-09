<?php

namespace robske_110\energymanagement\powertargetcontroller\tracker;

use robske_110\energymanagement\powertargetcontroller\provider\PowerProviderMode;

interface PowerProviderModeChangeListener{
	/**
	 * @param PowerProviderMode $oldMode
	 * @param PowerProviderMode $newMode
	 *
	 * @return bool Whether to allow (true) or block this PowerProviderMode change
	 */
	public function onPowerModeChange(PowerProviderMode $oldMode, PowerProviderMode $newMode): bool;
}