<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ShippingMode extends Enum
{
    const AIR_CONSOLIDATION = 'Air Consolidation';
    const SEA_FREIGHT = 'Sea Freight (LCL)';
    const INLAND = 'Inland';
    const DOOR_TO_AIRPORT = 'Door to Airport';
    const DOOR_TO_SEAPORT = 'Door to Seaport';
    const DOOR_TO_DOOR = 'Door to Door';
}
