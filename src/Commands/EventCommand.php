<?php

namespace App\Commands;


use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class EventCommand extends Command

{
    private $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    //комманда вызывается так: docker-compose run fpm bin/console event:update_status
    //команда установлена на сервере через крон, время срабатывания - каждый час
    protected function configure(): void
    {
        $this->setName('event:update_status')
            ->setDescription('updates the status of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //смена статуса регистрации с дефолтной true
        $this->check_registration();
        //смена статуса в процессе
        $this->check_in_process();
        $this->check_end_process();
        //смена статуса актуально
        $this->check_isActual_status();
    }

    protected function check_registration(): void
    {
        $qbEvent = $this->em->createQueryBuilder();
        $qbRegistration = $this->em->createQueryBuilder();

        //сначала находим id ивентов, у которых прошло время регистрации
        $qbEvent->select('d')->
        from(Event::class, 'd')->
        andWhere('d.deadline < CURRENT_DATE()');

        //записываем эти id
        /** @var Event[] $result */
        $result = $qbEvent->getQuery()->getResult();
        $eventsId = [];
        foreach ($result as $event1) {
            $eventsId[] = $event1->getId();
        }

        //изменяем статус registration у ивентов с этими id
        $qbRegistration->update(Event::class, 's')
            ->set('s.registration', 'false')
            ->andWhere('s.registration = true')
            ->andWhere($qbEvent->expr()->in('s.id', $eventsId))
            ->getQuery()->getResult();
    }

    protected function check_in_process(): void
    {
        $qbEventInProgress = $this->em->createQueryBuilder();
        $qbInProgress = $this->em->createQueryBuilder();

        //сначала находим id ивентов, у которых прошло время начала
        $qbEventInProgress->select('i')
            ->from(Event::class, 'i')
            ->where('i.date < CURRENT_DATE()');

        //записываем id
        /** @var Event[] $progress */
        $progress = $qbEventInProgress->getQuery()->getResult();
        $progressId = [];
        foreach ($progress as $event) {
            $progressId[] = $event->getId();
        }

        //изменяем
        $qbInProgress->update(Event::class, 'p')
            ->set('p.inProgress', 'true')
            ->where('p.inProgress = false')
            ->andWhere('p.registration = false')
            ->andWhere($qbEventInProgress->expr()->in('p.id', $progressId))
            ->getQuery()->getResult();
    }

    protected function check_end_process(): void
    {
        $qbEventEndProcess = $this->em->createQueryBuilder();
        $qbEndProcess = $this->em->createQueryBuilder();

        //сначала находим id ивентов, у которых прошло время окончания
        $qbEventEndProcess->select('id')
            ->from(Event::class, 'id')
            ->where('id.dateEnd < CURRENT_DATE()');

        //записываем id
        /** @var Event[] $endProgress */
        $endProgress = $qbEventEndProcess->getQuery()->getResult();
        $endProgressId = [];
        foreach ($endProgress as $event) {
            $endProgressId[] = $event->getId();
        }

        //изменяем
        $qbEndProcess->update(Event::class, 'e')->set('e.inProgress', 'false')
            ->where('e.inProgress = true')
            ->andWhere($qbEndProcess->expr()->in('e.id', $endProgressId))
            ->getQuery()->getResult();
    }

    protected function check_isActual_status(): void
    {
        //сложный запрос:
        $qb = $this->em->createQueryBuilder();
        //находим id ивентов,
        $qb->addSelect('e.id');
        //которые закончились,
        $qb->where('e.dateEnd < CURRENT_DATE()');
        //а также количество участников,
        $qb->addSelect('count(members.id) as m');
        //и количество заявок на окончание мероприятия
        $qb->addSelect('count(checkingRequests.id) as cr');
        //из объекста Event,
        $qb->from(Event::class, 'e');
        //также ищем такски по id ивентов, к которым они прикреплены,
        $qb->leftJoin('e.tasks', 'tasks');
        //участников
        $qb->leftJoin('tasks.members', 'members');
        //и заявки по id тасков,
        $qb->leftJoin('tasks.checkingRequests', 'checkingRequests');
        //группируем айди ивентов
        $qb->groupBy('e.id');
        $res = $qb->getQuery()->getResult();


        //через счётчик записываем в массив id ивентов, у таксков которых есть участники
        //лучше логику оставить в php, чем на запросы
        $i = 0;
        $eventId = [];

        while ($i !== count($res)) {
            if ($res[$i]['m'] > 0 || $res[$i]['cr'] > 0) {
                $eventId[] = $res[$i]['id'];
            }
            $i++;
        }

        //изменяем статус
        $qbIsActual = $this->em->createQueryBuilder();
        $qbIsActual->update(Event::class, 'e')
            ->set('e.isActual', 'false')
            ->where($qbIsActual->expr()->in('e.id', $eventId))
            //или если дата окончания меньше текущей даты+3дня (время закрытие тасков и заявок)
            ->orWhere("e.dateEnd < DATE_SUB(CURRENT_DATE(), 3,  'DAY')")
            ->andWhere('e.isActual = true')
            ->getQuery()->getResult();
    }
}