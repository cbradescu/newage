<?php

namespace CB\Bundle\NewAgeBundle\Extension\MassAction;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\PanelView;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;

class ReserveMassActionHandler implements MassActionHandlerInterface
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
    protected $responseMessage = 'cb.newage.reservation.datagrid.success_message';

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
//        $massAction = $args->getMassAction();
//        $options = $massAction->getOptions()->toArray();
//
//        $data = $args->getData();
//        $isAllSelected = $this->isAllSelected($data);
//
//        $offer = null;
//        if (array_key_exists('select-panel_view-grid', $data)) {
//            $offerId = $data['select-panel_view-grid']['offer'];
//
//            /** @var Offer $offer */
//            $offer = $this->entityManager->getRepository('CBNewAgeBundle:Offer')->findOneBy(['id' => $offerId]);
//        }
//
//        $panelViews = new ArrayCollection();
//        if (array_key_exists('values', $data)) {
//            $panelViewIds = explode(',', $data['values']);
//
//            $queryBuilder = $this
//                ->entityManager
//                ->getRepository('CBNewAgeBundle:PanelView')
//                ->createQueryBuilder('pv');
//
//            if ($isAllSelected)
//                $panelViewIds = $offer->getPanelViews()->map(function ($entity){
//                    /** @var PanelView $entity */
//                    return $entity->getId();
//                })->toArray();
//
//            $queryBuilder->andWhere($queryBuilder->expr()->in('pv.id', $panelViewIds));
//
//            $results = $queryBuilder->getQuery()->getResult();
//            foreach ($results as $entity){
//                $panelViews->add($entity);
//            }
//        }
//
//        return new MassActionResponse(
//            true,
//            null,
//            array(
//                'offer' => $offer,
//                'panelViews' => $panelViews,
//                'options' => $options
//            )
//        );
        $massAction = $args->getMassAction();
        $options = $massAction->getOptions()->toArray();

        $data = $args->getData();
        $isAllSelected = $this->isAllSelected($data);

        if (!array_key_exists('offer-items-grid', $data)) {
            throw new InvalidArgumentException('Datagrid is missing.');
        }

        if (!array_key_exists('offer', $data['offer-items-grid'])) {
            throw new InvalidArgumentException('Offer is missing.');
        }

        if (!array_key_exists('values', $data)) {
            throw new InvalidArgumentException('Values are missing.');
        }


        $offerId = $data['offer-items-grid']['offer'];

        /** @var Offer $offer */
        $offer = $this->entityManager->getRepository('CBNewAgeBundle:Offer')->findOneBy(['id' => $offerId]);

        return new MassActionResponse(
            true,
            null,
            array(
                'offer' => $offer,
                'values' => $data['values'],
                'isAllSelected' => $isAllSelected,
                'filters' => $data['filters'],
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
}
