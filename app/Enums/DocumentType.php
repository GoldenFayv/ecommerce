<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DocumentType extends Enum
{
    const INVOICE = 'Invoice';
    const PACKING_LIST = 'Packing_list';
    const PICTURES = 'Pictures';
    const COA = 'COA';
    const MSD = 'MSDS';
    const OTHER = 'Other';
}
