<?php

namespace SlaveMarket\Entity;

/**
 * Раб (Бедняга :-()
 *
 * @package SlaveMarket\Entity
 */
class Slave
{
    /**
     * Максимальное кол-во рабочих часов в сутки
     *
     * TODO: В идеале бы такое в общую настройку вынести - кол-во рабочих часов ещё где-то может быть использовано
     */
    public const DAY_WORK_HOURS = 16;

    /** @var int id раба */
    protected $id;

    /** @var string имя раба */
    protected $name;

    /** @var float Стоимость раба за час работы */
    protected $pricePerHour;

    /**
     * Slave constructor.
     *
     * @param int $id
     * @param string $name
     * @param float $pricePerHour
     */
    public function __construct(int $id, string $name, float $pricePerHour)
    {
        $this->id = $id;
        $this->name = $name;
        $this->pricePerHour = $pricePerHour;
    }

    /**
     * Возвращает id раба
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает имя раба
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает стоимость раба за час
     *
     * @return float
     */
    public function getPricePerHour(): float
    {
        return $this->pricePerHour;
    }
}