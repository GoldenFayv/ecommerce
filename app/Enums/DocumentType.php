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
    const INVOICE = 'invoice';
    const PACKING_LIST = 'packing_list';
    const PICTURES = 'pictures';
    const COA = 'coa';
    const MSD = 'msds';
    const OTHER = 'other';
}
