<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static HIGH()
 * @method static static NORMAL()
 */
final class PriorityLevel extends Enum
{
    const HIGH = 'High';
    const NORMAL = 'Normal';
}
