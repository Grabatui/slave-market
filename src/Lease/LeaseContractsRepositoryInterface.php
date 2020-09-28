<?php

namespace SlaveMarket\Lease;

/**
 * Интерфейс репозитория договоров аренды
 *
 * @package SlaveMarket\Lease
 */
interface LeaseContractsRepositoryInterface
{
    /**
     * Возвращает список договоров аренды для раба, в которых заняты часы из указанного периода
     *
     * @param int $slaveId
     * @param string $dateFrom
     * @param string $dateTo
     * @return LeaseContract[]
     */
    public function getForSlave(int $slaveId, string $dateFrom, string $dateTo) : array;
}