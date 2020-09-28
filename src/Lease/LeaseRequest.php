<?php

namespace SlaveMarket\Lease;

use Carbon\Carbon;

/**
 * Запрос на аренду раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseRequest
{
    /** @var int id хозяина */
    public $masterId;

    /** @var int id раба */
    public $slaveId;

    /** @var Carbon время начала работ */
    protected $timeFrom;

    /** @var Carbon время окончания работ */
    protected $timeTo;

    public function getTimeFrom(): Carbon
    {
        return $this->timeFrom;
    }

    public function getTimeTo(): Carbon
    {
        return $this->timeTo;
    }

    public function setTimeFrom(string $time): void
    {
        $this->timeFrom = Carbon::createFromFormat('Y-m-d H:i:s', $time);
    }

    public function setTimeTo(string $time): void
    {
        $this->timeTo = Carbon::createFromFormat('Y-m-d H:i:s', $time);
    }
}