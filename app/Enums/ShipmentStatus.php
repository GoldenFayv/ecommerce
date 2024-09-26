<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static APPROVED()
 * @method static static REJECTED()
 * @method static static PENDING()
 */
final class ShipmentStatus extends Enum
{
    const APPROVED = 'Approved';
    const REJECTED = 'Rejected';
    const PENDING = 'Pending';
}
