<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ShippingMethod extends Enum
{
    const LAND = 'Land';
    const AIR = 'Air';
    const OCEAN = 'Ocean';
    const RAIL = 'Rail';
}
