<?php

declare(STRICT_TYPES=1);

interface DebtCollector {
    public function collectDebt(float $owedAmount): float;
}

class CollectionAgency implements DebtCollector {
    public function collectDebt(float $owedAmount): float {
        return $owedAmount * 2.0;
    }
};

$collector = new CollectionAgency();

echo $collector->collectDebt(100.0);