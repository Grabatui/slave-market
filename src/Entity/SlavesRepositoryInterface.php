<?php

namespace SlaveMarket\Entity;

/**
 * Интерфейс репозиториев рабов
 *
 * @package SlaveMarket\Entity
 */
interface SlavesRepositoryInterface
{
    /**
     * Возвращает раба по его id
     *
     * @param int $id
     * @return Slave|null
     */
    public function getById(int $id): ?Slave;

    public function getWorkHours(int $id): array;
}