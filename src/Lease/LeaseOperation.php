<?php

namespace SlaveMarket\Lease;

use Carbon\CarbonInterval;
use SlaveMarket\Entity\Master;
use SlaveMarket\Entity\MastersRepositoryInterface;
use SlaveMarket\Entity\Slave;
use SlaveMarket\Entity\SlavesRepositoryInterface;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 *
 * TODO: Класс написан в спешке и выглядит убого и неприбрано - каюсь
 */
class LeaseOperation
{
    /**
     * @var LeaseContractsRepositoryInterface
     */
    protected $contractsRepository;

    /**
     * @var MastersRepositoryInterface
     */
    protected $mastersRepository;

    /**
     * @var SlavesRepositoryInterface
     */
    protected $slavesRepository;

    /**
     * @var LeaseRequest
     */
    private $request;

    /**
     * @var LeaseResponse
     */
    private $response;

    /**
     * @var Master|null
     */
    private $master;

    /**
     * @var Slave|null
     */
    private $slave;

    /**
     * LeaseOperation constructor.
     *
     * @param LeaseContractsRepositoryInterface $contractsRepo
     * @param MastersRepositoryInterface $mastersRepo
     * @param SlavesRepositoryInterface $slavesRepo
     */
    public function __construct(
        LeaseContractsRepositoryInterface $contractsRepo,
        MastersRepositoryInterface $mastersRepo,
        SlavesRepositoryInterface $slavesRepo
    )
    {
        $this->contractsRepository = $contractsRepo;
        $this->mastersRepository = $mastersRepo;
        $this->slavesRepository = $slavesRepo;
    }

    /**
     * Выполнить операцию
     *
     * @param LeaseRequest $request
     * @return LeaseResponse
     */
    public function run(LeaseRequest $request): LeaseResponse
    {
        $this->request = $this->formatRequest($request);
        $this->response = new LeaseResponse();

        $this->setMaster($this->request->masterId);
        $this->setSlave($this->request->slaveId);

        $this->checkEntities();

        if ($this->response->hasErrors()) {
            return $this->response;
        }

        $this->checkLeaseDates();

        if ($this->response->hasErrors()) {
            return $this->response;
        }

        $this->response->setLeaseContract($this->makeContract());

        return $this->response;
    }

    /**
     * Обнуляем минуты и секунды, чтобы время считалось только по выбранным часам
     *
     * @param LeaseRequest $request
     * @return LeaseRequest
     */
    protected function formatRequest(LeaseRequest $request): LeaseRequest
    {
        $request->setTimeFrom($request->getTimeFrom()->minute(0)->second(0)->format('Y-m-d H:i:s'));
        $request->setTimeTo($request->getTimeTo()->minute(0)->second(0)->format('Y-m-d H:i:s'));

        return $request;
    }

    /**
     * Устанавливаем хозяина
     *
     * @param int $masterId
     */
    protected function setMaster(int $masterId): void
    {
        $this->master = $this->mastersRepository->getById($masterId);
    }

    /**
     * Устанавливаем раба
     *
     * @param int $slaveId
     */
    protected function setSlave(int $slaveId): void
    {
        $this->slave = $this->slavesRepository->getById($slaveId);
    }

    /**
     * Проверяем, что хозяин и раб с такими id вообще существуют
     */
    protected function checkEntities(): void
    {
        if (!($this->master instanceof Master) || !($this->slave instanceof Slave)) {
            $this->response->addError('Неверные входные данные');
        }
    }

    /**
     * Проверяем основные ограничения по времени
     */
    protected function checkLeaseDates(): void
    {
        $interval = $this->getHoursInterval();

        // Если мы пытаемся в один и тот же день арендовать более 16и часов
        if ($this->request->getTimeFrom()->isSameDay($this->request->getTimeTo()) && $interval->h > Slave::DAY_WORK_HOURS) {
            $this->response->addError(sprintf(
                'Ошибка. Дико, но рабу #%d "%s" тоже необходимо поспать',
                $this->slave->getId(),
                $this->slave->getName()
            ));
        }

        $hours = $this->getBusySlaveHours();

        if (!empty($hours)) {
            $this->response->addError(sprintf(
                'Ошибка. Раб #%d "%s" занят. Занятые часы: %s',
                $this->slave->getId(),
                $this->slave->getName(),
                implode(', ', array_map(function (LeaseHour $hour): string {
                    return sprintf('"%s"', $hour->getDateString());
                }, $hours))
            ));
        }
    }

    /**
     * Формируем контракт
     *
     * @return LeaseContract
     */
    protected function makeContract(): LeaseContract
    {
        $slave = $this->makeLeasedSlave();

        return new LeaseContract($this->master, $this->slave, $slave->getHoursPrice(), $slave->getHours());
    }

    /**
     * Формирвем арендованного раба
     *
     * @return LeasedSlave
     */
    private function makeLeasedSlave(): LeasedSlave
    {
        return new LeasedSlave($this->slave, $this->request->getTimeFrom(), $this->request->getTimeTo());
    }

    /**
     * Разница между первым и последним часом аренды
     *
     * @return CarbonInterval
     */
    private function getHoursInterval(): CarbonInterval
    {
        return $this->request->getTimeFrom()->diffAsCarbonInterval($this->request->getTimeTo());
    }

    /**
     * Возвращаем уже занатые часы раба, на которые претендует текущий хозяин
     *
     * @return array
     */
    private function getBusySlaveHours(): array
    {
        $contracts = $this->contractsRepository->getForSlave(
            $this->slave->getId(),
            $this->request->getTimeFrom()->format('Y-m-d'),
            $this->request->getTimeTo()->format('Y-m-d')
        );

        $hours = [];
        foreach ($contracts as $contract) {
            // Если хозяин контракта не ВИП, а текущий - ВИП, - мы считаем часы его рабов свободными
            if (!$contract->master->isVIP() && $this->master->isVIP()) {
                continue;
            }

            // Возврашаем только те часы, что требуются текущим хозяином
            $hours = array_merge(
                $hours,
                $contract->getLeasedBusyHours($this->request->getTimeFrom(), $this->request->getTimeTo())
            );
        }

        return $hours;
    }
}