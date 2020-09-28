<?php

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use SlaveMarket\Entity\Master;
use SlaveMarket\Entity\MastersRepositoryInterface;
use SlaveMarket\Entity\Slave;
use SlaveMarket\Entity\SlavesRepositoryInterface;

/**
 * Тесты операции аренды раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperationTest extends TestCase
{
    /**
     * Stub репозитория хозяев
     *
     * @param Master[] ...$masters
     * @return MastersRepositoryInterface
     */
    private function makeFakeMasterRepository(...$masters): MastersRepositoryInterface
    {
        /** @var MastersRepositoryInterface $mastersRepository */
        $mastersRepository = $this->prophesize(MastersRepositoryInterface::class);
        foreach ($masters as $master) {
            $mastersRepository->getById($master->getId())->willReturn($master);
        }

        return $mastersRepository->reveal();
    }

    /**
     * Stub репозитория рабов
     *
     * @param Slave[] $slaves
     * @return SlavesRepositoryInterface
     */
    private function makeFakeSlaveRepository(...$slaves): SlavesRepositoryInterface
    {
        /** @var SlavesRepositoryInterface $slavesRepository */
        $slavesRepository = $this->prophesize(SlavesRepositoryInterface::class);
        foreach ($slaves as $slave) {
            $slavesRepository->getById($slave->getId())->willReturn($slave);
        }

        return $slavesRepository->reveal();
    }

    /**
     * Если раб занят, то арендовать его не получится
     */
    public function test_periodIsBusy_failedWithOverlapInfo()
    {
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $master2 = new Master(2, 'сэр Вонючка');
            $masterRepo = $this->makeFakeMasterRepository($master1, $master2);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

            // Договор аренды. 1й хозяин арендовал раба
            $leaseContract1 = new LeaseContract($master1, $slave1, 80, [
                new LeaseHour('2017-01-01 00'),
                new LeaseHour('2017-01-01 01'),
                new LeaseHour('2017-01-01 02'),
                new LeaseHour('2017-01-01 03'),
            ]);

            // Stub репозитория договоров
            /** @var LeaseContractsRepositoryInterface|ObjectProphecy $contractsRepo */
            $contractsRepo = $this->prophesize(LeaseContractsRepositoryInterface::class);
            $contractsRepo->getForSlave($slave1->getId(), '2017-01-01', '2017-01-01')->willReturn([$leaseContract1]);

            // Запрос на новую аренду. 2й хозяин выбрал занятое время
            $leaseRequest = new LeaseRequest();
            $leaseRequest->masterId = $master2->getId();
            $leaseRequest->slaveId = $slave1->getId();
            $leaseRequest->setTimeFrom('2017-01-01 01:30:00');
            $leaseRequest->setTimeTo('2017-01-01 02:01:00');

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo->reveal(), $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $expectedErrors = ['Ошибка. Раб #1 "Уродливый Фред" занят. Занятые часы: "2017-01-01 01", "2017-01-01 02"'];

        $this->assertArraySubset($expectedErrors, $response->getErrors());
        $this->assertNull($response->getLeaseContract());
    }

    /**
     * Если раб бездельничает, то его легко можно арендовать
     */
    public function test_idleSlave_successfullyLeased()
    {
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $masterRepo = $this->makeFakeMasterRepository($master1);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

            /** @var LeaseContractsRepositoryInterface $contractsRepo */
            $contractsRepo = $this->prophesize(LeaseContractsRepositoryInterface::class);
            $contractsRepo->getForSlave($slave1->getId(), '2017-01-01', '2017-01-01')->willReturn([]);

            // Запрос на новую аренду
            $leaseRequest = new LeaseRequest();
            $leaseRequest->masterId = $master1->getId();
            $leaseRequest->slaveId = $slave1->getId();
            $leaseRequest->setTimeFrom('2017-01-01 01:30:00');
            $leaseRequest->setTimeTo('2017-01-01 02:01:00');

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo->reveal(), $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $this->assertEmpty($response->getErrors());
        $this->assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        $this->assertEquals(40, $response->getLeaseContract()->price);
    }
}
