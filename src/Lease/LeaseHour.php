<?php

namespace SlaveMarket\Lease;

use Carbon\Carbon;

/**
 * Арендованный час
 *
 * @package SlaveMarket\Lease
 */
class LeaseHour
{
    /**
     * Время начала часа
     *
     * @var Carbon
     */
    protected $dateTime;

    /**
     * LeaseHour constructor.
     *
     * @param string $dateTime Y-m-d H
     */
    public function __construct(string $dateTime)
    {
        $this->dateTime = Carbon::createFromFormat('Y-m-d H', $dateTime);
    }

    /**
     * Возвращает строку, представляющую час
     *
     * @return string
     */
    public function getDateString(): string
    {
        return $this->dateTime->format('Y-m-d H');
    }

    /**
     * Возвращает объект даты
     *
     * @return Carbon
     */
    public function getDateTime(): Carbon
    {
        return $this->dateTime;
    }

    /**
     * Возвращает день аренды
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->dateTime->format('Y-m-d');
    }

    /**
     * Возвращает час аренды
     *
     * @return string
     */
    public function getHour(): string
    {
        return $this->dateTime->hour;
    }
}
