<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\calculator\adjustment;

use robske_110\energymanagement\powertargetcontroller\calculator\PowerTargetAdjustment;

/** PowerUpperLimit always has higher priority than the PowerLowerLimit */
interface PowerUpperLimit extends PowerTargetAdjustment{

}