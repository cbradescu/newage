<?php

namespace CB\Bundle\NewAgeBundle\EventListener\DataGrid;

use CB\Bundle\NewAgeBundle\Entity\Reservation;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityMergeBundle\Metadata\MetadataRegistry;

class ConfirmMassActionListener
{
    /**
     * @var MetadataRegistry
     */
    protected $metadataRegistry;

    /** @var EntityManager */
    protected $entityManager;


    /**
     * @param MetadataRegistry $metadataRegistry
     */
    public function __construct(MetadataRegistry $metadataRegistry, EntityManager $entityManager)
    {
        $this->metadataRegistry = $metadataRegistry;
        $this->entityManager = $entityManager;
    }

    /**
     * Remove confirm mass action if entity config mass action is enabled and
     * there is a confirmed scheduler event for current reservation.
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $massActions = isset($config['mass_actions']) ? $config['mass_actions'] : array();

        if (empty($massActions['confirm']['entity_name'])) {
            return;
        }

        $entityName = $massActions['confirm']['entity_name'];

        $entityConfirmEnable = $this->metadataRegistry->getEntityMetadata($entityName)->is('enable');

        if (!$entityConfirmEnable) {
            $params = $event->getDatagrid()->getParameters();

            $reservationId = $params->get('reservation') ? $params->get('reservation') : null;

            /** @var Reservation $reservation */
            $reservation = $this->entityManager->getRepository('CBNewAgeBundle:Reservation')->findOneBy(['id' => $reservationId]);

            if ($reservation) {
                $reservedPanelViews = $reservation->getReservedPanelViews();

                foreach ($reservedPanelViews as $reservedPanelView) {
                    $event = $reservation->findEventBy($reservation->getAttributes($reservedPanelView));

                    if ($event) {
                        if ($event->getStatus() == SchedulerEvent::CONFIRMED)
                        $config->offsetUnsetByPath('[mass_actions][confirm]');
                        return;
                    }
                }
            }
        }
    }
}
