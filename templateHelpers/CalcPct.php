<?php
namespace Espo\Custom\TemplateHelpers;

use Espo\Core\Htmlizer\Helper;
use Espo\Core\Htmlizer\Helper\Data;
use Espo\Core\Htmlizer\Helper\Result;

class CalcPct implements Helper
{
    public function __construct(
        // Pass needed dependencies.
    ) {

    }

    public function render(Data $data): Result
    {

        $val = $data->getArgumentList()[1] ?? '';
        $pct = $data->getArgumentList()[0] ?? '';
        $calcResult = $pct != 0 ? $val * ($pct / 100) : null;

        if (floor($calcResult) == $calcResult) {
                return Result::createSafeString(number_format($calcResult, 0, '.', ''));
        }

        return Result::createSafeString(number_format($calcResult, 2, '.', ''));

    }
}
