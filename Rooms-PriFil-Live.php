<?php
namespace Espo\Custom\Classes\Select\Rooms\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Live implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
          'AND' => [['let' => true],['activeTenancy' => true]]
        ]);
    }
}
