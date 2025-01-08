<?php
namespace Espo\Custom\Classes\Select\Rooms\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class All implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
          'OR' => [['let' => true],['let' => false]]
        ]);
    }
}
