<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ORIGIN()
 * @method static static DESTINATION()
 */
final class AddressType extends Enum
{
    const ORIGIN = 'Origin';
    const DESTINATION = 'Destination';
}
