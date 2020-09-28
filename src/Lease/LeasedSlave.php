<?php

namespace SlaveMarket\Lease;

use Carbon\Carbon;
use SlaveMarket\Entity\Slave;

class LeasedSlave
{
    /**
     * @var Slave
     */
    private $slave;

    /**
     * @var Carbon
     */
    private $dateFrom;

    /**
     * @var Carbon
     */
    private $dateTo;

    /**
     * @var LeaseHour[]
     */
    private $hours;

    public function __construct(Slave $slave, Carbon $dateFrom, Carbon $dateTo)
    {
        $this->slave = $slave;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

        $this->calculateHours();
    }

    /**
     * Количество забронированных
     *
     * @return array
     */
    public function getHours(): array
    {
        return $this->hours;
    }

    /**
     * Цена за забронированные часы
     *
     * @return float
     */
    public function getHoursPrice(): float
    {
        return count($this->getHours()) * $this->slave->getPricePerHour();
    }

    /**
     * Подсчитываем забронированные часы
     */
    private function calculateHours(): void
    {
        $this->hours = [];
        // TODO: У Carbon явно есть решение более простое, но времени мало разбираться
        for ($hour = $this->dateFrom; $hour->lessThanOrEqualTo($this->dateTo); $hour->addHour()) {
            $this->hours[] = new LeaseHour($hour->format('Y-m-d H'));
        }
    }
}
