<?php

namespace CB\Bundle\NewAgeBundle\Extension\MassAction;

use CB\Bundle\NewAgeBundle\Entity\Offer;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\EntityMergeBundle\Exception\InvalidArgumentException;

class OfferMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $massAction = $args->getMassAction();
        $options = $massAction->getOptions()->toArray();

        $data = $args->getData();
        $isAllSelected = $this->isAllSelected($data);

        if (!array_key_exists('available-panel_view-grid', $data)) {
            throw new InvalidArgumentException('Datagrid is missing.');
        }

        if (!array_key_exists('offer', $data['available-panel_view-grid'])) {
            throw new InvalidArgumentException('Offer is missing.');
        }

        if (!array_key_exists('values', $data)) {
            throw new InvalidArgumentException('Values are missing.');
        }


        $offerId = $data['available-panel_view-grid']['offer'];

        /** @var Offer $offer */
        $offer = $this->entityManager->getRepository('CBNewAgeBundle:Offer')->findOneBy(['id' => $offerId]);

        return new MassActionResponse(
            true,
            null,
            array(
                'offer' => $offer,
                'values' => $data['values'],
                'isAllselected' => $isAllSelected,
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
