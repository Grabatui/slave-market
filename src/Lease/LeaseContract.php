<?php

namespace SlaveMarket\Lease;

use Carbon\Carbon;
use SlaveMarket\Entity\Master;
use SlaveMarket\Entity\Slave;

/**
 * Договор аренды
 *
 * @package SlaveMarket\Lease
 */
class LeaseContract
{
    /** @var Master Хозяин */
    public $master;

    /** @var Slave Раб */
    public $slave;

    /** @var float Стоимость */
    public $price = 0;

    /** @var LeaseHour[] Список арендованных часов */
    public $leasedHours = [];

    public function __construct(Master $master, Slave $slave, float $price, array $leasedHours)
    {
        $this->master = $master;
        $this->slave = $slave;
        $this->price = $price;
        $this->leasedHours = $leasedHours;
    }

    /**
     * Возвращаем занятые относительно передаваемых границ времени часы
     *
     * @param Carbon $timeFrom
     * @param Carbon $timeTo
     * @return array
     */
    public function getLeasedBusyHours(Carbon $timeFrom, Carbon $timeTo): array
    {
        return array_filter($this->leasedHours, function (LeaseHour $hour) use ($timeFrom, $timeTo): bool {
            return $hour->getDateTime()->between($timeFrom, $timeTo);
        });
    }
}
