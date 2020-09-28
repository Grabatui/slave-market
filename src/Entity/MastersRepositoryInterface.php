<?php

namespace SlaveMarket\Entity;

/**
 * Интерфейс репозиториев хозяев
 *
 * @package SlaveMarket\Entity
 */
interface MastersRepositoryInterface
{
    /**
     * Возвращает хозяина по его id
     *
     * @param int $id
     * @return Master|null
     */
    public function getById(int $id) : ?Master;
}