<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class PanelViewRepository extends EntityRepository
{
    /** @var RequestStack $requestStack */
    protected $requestStack;

    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Returns a query builder which can be used to get list of panel views
     *
     * @param int $organizationId
     *
     * @return QueryBuilder
     */
    public function getPanelViewsQueryBuilder($organizationId)
    {
        $qb = $this->createQueryBuilder('pv')
            ->select(
                'pv.id',
                'pv.name',
                'p.name as panelName',
                'IDENTITY(p.supportType)',
                'IDENTITY(p.lightingType)',
                'c.name as city'
            )
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.addresses', 'a')
            ->leftJoin('a.city', 'c')
            ->where('pv.organization = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->addOrderBy('p.name', 'ASC')
            ->addOrderBy('pv.name', 'ASC')
    ;

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get list of reserved panel views on a specific reservation and their status.
     *
     * @param int $reservationId
     *
     * @return QueryBuilder
     */
    public function getReservedPanelViewsWithStatusQueryBuilder()
    {
        $qb = $this->createQueryBuilder('pv')
            ->select(
                'pv.id',
                '(:reservation) as rid',
                'pv.name',
                'pv.url as panelViewUrl',
                'p.name as panel',
                'p.dimensions',
                'st.name as support',
                'lt.name as lighting',
                'c.name as city',
                'CONCAT(a.street,  CONCAT(\' \', a.street2)) as address',
                'MAX(CASE WHEN (ev.id IS NULL) THEN
                    0
                  ELSE
                    ev.status
                  END) as available'
            )
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.supportType', 'st')
            ->leftJoin('p.lightingType', 'lt')
            ->leftJoin('p.addresses', 'a')
            ->leftJoin('a.city', 'c')
            ->innerJoin('pv.reservations', 'r')
            ->leftJoin('r.offer', 'o')
            ->leftJoin(
                'pv.events',
                'ev',
                'WITH',
                '(ev.start >= o.start AND ev.start <= o.end) OR (ev.end >= o.start AND ev.end <= o.end) OR (ev.start >= o.start AND ev.end <= o.end) OR (ev.start <= o.start AND ev.end >= o.end)'
            )
            ->where(':reservation MEMBER OF pv.reservations')
            ->groupBy('pv.id')
        ;

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get list of free panel views (can be reserved but NOT confirmed) .
     *
     * @return QueryBuilder
     */
    public function getFreePanelViewsQueryBuilder()
    {
        // List of confirmed panel views in a specific period.
        $evQb = $this->getEntityManager()->getRepository('CBSchedulerBundle:SchedulerEvent')->createQueryBuilder('ev')
            ->select('IDENTITY(ev.panelView)')
            ->where('(ev.start >= :start AND ev.start <= :end) OR (ev.end >= :start AND ev.end <= :end) OR (ev.start >= :start AND ev.end <= :end) OR (ev.start <= :start AND ev.end >= :end)')
            ->andWhere('ev.status=' . SchedulerEvent::CONFIRMED)
        ;

        $qb = $this->createQueryBuilder('pv')
            ->select(
                'pv.id',
                'pv.url',
                'pv.name',
                'p.name as panel',
                'p.dimensions',
                'st.name as support',
                'lt.name as lighting',
                'c.name as city',
                'CONCAT(a.street,  CONCAT(\' \', a.street2)) as address',
                '(CASE WHEN (:offer IS NOT NULL) THEN
                    CASE WHEN (:offer MEMBER OF pv.offers OR pv.id IN (:data_in)) AND pv.id NOT IN (:data_not_in)
                    THEN true ELSE false END
                  ELSE
                    CASE WHEN pv.id IN (:data_in) AND pv.id NOT IN (:data_not_in)
                    THEN true ELSE false END
                  END) as hasContact'
            )
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.supportType', 'st')
            ->leftJoin('p.lightingType', 'lt')
            ->leftJoin('p.addresses', 'a')
            ->leftJoin('a.city', 'c')
            ->where('pv.id NOT IN (' . $evQb->getDQL() . ')')
        ;

        return $qb;
    }

    /**
     * Return confirmed Panel Views in an interval of time.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return QueryBuilder
     */
    public function getConfirmedPanelViews($start, $end)
    {
        $qb = $this->getEntityManager()->getRepository('CBSchedulerBundle:SchedulerEvent')->createQueryBuilder('ev')
            ->select(
                'IDENTITY(ev.panelView) as panelView',
                'ev.start',
                'ev.end'
                )
            ->where('(ev.start >= :start AND ev.start <= :end) OR (ev.end >= :start AND ev.end <= :end) OR (ev.start >= :start AND ev.end <= :end) OR (ev.start <= :start AND ev.end >= :end)')
            ->andWhere('ev.status=' . SchedulerEvent::CONFIRMED)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $qb;
    }

    /**
     * @param Offer $offer
     * @return array
     */
    public function getForbiddenPanelViewIds(Offer $offer)
    {
        $forbiddenPanelViewsIds = [];

        $results = $this->getConfirmedPanelViews($offer->getStart(), $offer->getEnd())->getQuery()->getResult();

        $confirmedPanelViews = [];
        foreach ($results as $row) {

            $confirmedPanelViews[$row['panelView']][] = [
                'start' => $row['start'],
                'end' => $row['end']
            ];
        }

        foreach ($confirmedPanelViews as $panelViewId => $intervals) {
            $panelView = $this->findOneBy(['id' => $panelViewId]);

            /** @var PanelView $panelView */
            $freeIntervals = $panelView->getFreeIntervals($intervals, $offer->getStart(), $offer->getEnd());

            $forbidden = true;

            if (count($freeIntervals) > 0) {
                /** @var array $freeInterval */
                foreach ($freeIntervals as $freeInterval) {
                    /** @var \DateInterval $interval */
                    $interval = $freeInterval['end']->diff($freeInterval['start']);
                    if ($interval->format('%a') >= 7)
                        $forbidden = false;
                }
            }

            if ($forbidden)
                $forbiddenPanelViewsIds[] = $panelView->getId();
        }

        return $forbiddenPanelViewsIds;
    }

    /**
     * @param Offer $id
     * @return QueryBuilder
     */
    public function getAvailablePanelViewsQueryBuilder()
    {
        $request = $this->requestStack->getCurrentRequest();
        $offer  = $request->get('offer');

        $qb = $this->createQueryBuilder('pv')
            ->select(
                'pv.id',
                'pv.name as panelView',
                'pv.url',
                'pv.sketch',
                'p.name as panel',
                'p.dimensions',
                'p.neighborhoods',
                'st.name as support',
                'lt.name as lighting',
                'c.name as city',
                'CONCAT(addr.street,  CONCAT(\' \', addr.street2)) as address',
                'CONCAT(addr.latitude,  CONCAT(\',\', addr.longitude)) as gps'
            )
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.supportType', 'st')
            ->leftJoin('p.lightingType', 'lt')
            ->leftJoin('p.addresses', 'addr')
            ->leftJoin('addr.city', 'c');

        if ($offer) {
            $forbiddenPanelViewsIds = $this->getForbiddenPanelViewIds($offer);

            if (count($forbiddenPanelViewsIds) > 0)
                $qb->where($qb->expr()->notIn('pv.id', $forbiddenPanelViewsIds));
        }

        return $qb;
    }
}