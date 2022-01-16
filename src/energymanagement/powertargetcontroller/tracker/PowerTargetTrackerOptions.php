<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\tracker;

class PowerTargetTrackerOptions{
	/**
	 * @var string The tracking mode (algorithm) to use for PowerTarget tracking.
	 * Possible modes are: minDiff, keepBelow, keepAbove
	 */
	public string $selectedTrackingMode = "minDiff";

	/**
	 * @var float The leniency to give in keepBelow mode.
	 */
	public float $keepBelowAllowAbove = 0;
	/**
	 * @var float The leniency to give in keepAbove mode.
	 */
	public float $keepAboveAllowBelow = 0;

	public function get(string $option): mixed{
		return match($option){
			"mode.selected" => $this->selectedTrackingMode,
			"mode.keepBelow.allowAbove" => $this->keepBelowAllowAbove,
			"mode.keepAbove.allowBelow" => $this->keepAboveAllowBelow
		};
	}
}