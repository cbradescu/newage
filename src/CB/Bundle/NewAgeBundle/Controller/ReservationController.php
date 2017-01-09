<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 23/Nov/16
 * Time: 12:43
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\EntityMergeBundle\Data\EntityData;
use Oro\Bundle\EntityMergeBundle\Data\EntityDataFactory;

/**
 * @Route("/reservation")
 */
class ReservationController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_reservation_index")
     * @Template()
     * @AclAncestor("cb_newage_reservation_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_reservation_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_reservation_view")
     */
    public function viewAction(Reservation $reservation)
    {
        return [
            'entity' => $reservation
        ];
    }

    /**
     * @Route("/create/{id}", name="cb_newage_reservation_create", requirements={"id"="\d+"})
     * @AclAncestor("cb_newage_reservation_create")
     * @Template("CBNewAgeBundle:Reservation:view.html.twig")
     *
     * @param Offer $offer
     * @return array
     */
    public function createAction(Offer $offer)
    {
        /** @var Reservation $reservation */
        $reservation = $this->get('cb_newage_reservation.manager')->createReservation();

        return $this->update($reservation);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_reservation_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_reservation_update")
     *
     * @param Reservation $reservation
     * @return array
     */
    public function updateAction(Reservation $reservation)
    {
        return $this->update($reservation);
    }

    /**
     * @param Reservation $reservation
     *
     * @return array
     */
    protected function update(Reservation $reservation)
    {
        if ($this->get('cb_newage_reservation.form.handler.entity')->process($reservation)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.reservation.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_reservation_update', 'parameters' => ['id' => $reservation->getId()]],
                ['route' => 'cb_newage_reservation_view', 'parameters' => ['id' => $reservation->getId()]]
            );
        }

        return array(
            'entity' => $reservation,
            'form' => $this->get('cb_newage_reservation.form.entity')->createView()

        );
    }

    /**
     * @Route("/{gridName}/reserveMassAction/{actionName}", name="cb_reserve_massaction")
     * @AclAncestor("cb_newage_reservation_create")
     * @Template("CBNewAgeBundle:Reservation:update.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return array
     */
    public function reserveMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->getRequest());

        $offer = $response->getOption('offer');
        $panelViews = $response->getOption('panelViews');

        /** @var Reservation $reservation */
        $reservation = $this->get('cb_newage_reservation.manager')->createReservation();
        $reservation->setOffer($offer);
        $reservation->setReservedPanelViews($panelViews);

        return $this->update($reservation);
    }


    /**
     * @Route("/{gridName}/confirmMassAction/{actionName}", name="cb_confirm_massaction")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return JsonResponse
     */
    public function confirmMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->getRequest());

        $data = [
            'successful' => $response->isSuccessful(),
            'message' => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }
}