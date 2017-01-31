<?php

namespace CB\Bundle\NewAgeBundle\Extension\MassAction;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\PanelView;

use CB\Bundle\NewAgeBundle\Entity\Reservation;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;

class ConfirmMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $responseMessage = 'cb.newage.reservation.datagrid.confirm.success_message';

    /**
     * @param EntityManager $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManager $entityManager,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
//        $data = $args->getData();
//
//        $massAction = $args->getMassAction();
//        $options = $massAction->getOptions()->toArray();
//
//        $iteration = 0;
//
//        $this->entityManager->beginTransaction();
//        try {
//
//            $isAllSelected = $this->isAllSelected($data);
//
//            $reservation = null;
//            if (array_key_exists('reservation-with-confirm-panel_view-grid', $data)) {
//                $reservationId = $data['reservation-with-confirm-panel_view-grid']['reservation'];
//
//                /** @var Reservation $reservation */
//                $reservation = $this->entityManager->getRepository('CBNewAgeBundle:Reservation')->findOneBy(['id' => $reservationId]);
//            }
//
//            if (array_key_exists('values', $data)) {
//                $panelViewIds = explode(',', $data['values']);
//
//                $queryBuilder = $this
//                    ->entityManager
//                    ->getRepository('CBNewAgeBundle:PanelView')
//                    ->createQueryBuilder('pv');
//
//                if ($isAllSelected)
//                    $panelViewIds = $reservation->getReservedPanelViews()->map(function ($entity) {
//                        /** @var PanelView $entity */
//                        return $entity->getId();
//                    })->toArray();
//
//                $queryBuilder->andWhere($queryBuilder->expr()->in('pv.id', $panelViewIds));
//
//                $results = $queryBuilder->getQuery()->getResult();
//                foreach ($results as $entity) {
//                    /** @var SchedulerEvent $event */
//                    $event = $this->entityManager->getRepository('CBSchedulerBundle:SchedulerEvent')->findOneBy(
//                        [
//                            'reservation' => $reservation->getId(),
//                            'panelView' => $entity->getId()
//                        ]
//                    );
//
//                    if ($event) {
//                        $event->setStatus(SchedulerEvent::ACCEPTED);
//                        $this->entityManager->flush($event);
//                        $iteration++;
//                    }
//                }
//            }
//
//            $this->entityManager->commit();
//        } catch (\Exception $e) {
//            $this->entityManager->rollback();
//            throw $e;
//        }
//
//        return $this->getResponse($args, $iteration);

        $massAction = $args->getMassAction();
        $options = $massAction->getOptions()->toArray();

        $data = $args->getData();
        $isAllSelected = $this->isAllSelected($data);

        if (!array_key_exists('reservation-items-grid', $data)) {
            throw new \InvalidArgumentException('Datagrid is missing.');
        }

        if (!array_key_exists('offer', $data['reservation-items-grid'])) {
            throw new \InvalidArgumentException('Offer is missing.');
        }

        if (!array_key_exists('values', $data)) {
            throw new \InvalidArgumentException('Values are missing.');
        }


        $offerId = $data['reservation-items-grid']['offer'];

        /** @var Offer $offer */
        $offer = $this->entityManager->getRepository('CBNewAgeBundle:Offer')->findOneBy(['id' => $offerId]);

        return new MassActionResponse(
            true,
            null,
            array(
                'offer' => $offer,
                'values' => $data['values'],
                'isAllSelected' => $isAllSelected,
                'filters' => isset($data['filters']) ? $data['filters'] : null,
                'options' => $options
            )
        );
    }


    /**
     * @param array $data
     * @return bool
     */
    protected function isAllSelected($data)
    {
        return array_key_exists('inset', $data) && $data['inset'] === '0';
    }

    /**
     * @param MassActionHandlerArgs $args
     * @param int $entitiesCount
     *
     * @return MassActionResponse
     */
    protected function getResponse(MassActionHandlerArgs $args, $entitiesCount = 0)
    {
        $massAction      = $args->getMassAction();
        $responseMessage = $massAction->getOptions()->offsetGetByPath('[messages][success]', $this->responseMessage);

        $successful = $entitiesCount > 0;
        $options    = ['count' => $entitiesCount];

        return new MassActionResponse(
            $successful,
            $this->translator->transChoice(
                $responseMessage,
                $entitiesCount,
                ['%count%' => $entitiesCount]
            ),
            $options
        );
    }
}
