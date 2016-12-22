<?php

namespace CB\Bundle\NewAgeBundle\EventListener\DataGrid;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ReserveMassActionListener
{
    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setRequest(ContainerInterface $container) {

        $this->request = $container->get('request');
    }

    /**
     * Remove mass action if entity config mass action disabled
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $datagrid = $event->getDatagrid();

        if ($datagrid->getName() == 'selected-panel_view-grid') {
            if ($this->request->get('_route') == 'cb_newage_reservation_view')
                $config->offsetUnsetByPath('[mass_actions][reserve]');

            if ($this->request->get('_route') == 'cb_newage_offer_view') {
                /** @var Offer $offer */
                $offer = $this->entityManager->getRepository('CBNewAgeBundle:Offer')->findOneBy(['id' => $this->request->get('id')]);

                if ($offer && ($offer->getReservation() != null || !$offer->getWorkflowStep()->isFinal())) {
                    $config->offsetUnsetByPath('[mass_actions][reserve]');
                }
            }
        }
    }
}
