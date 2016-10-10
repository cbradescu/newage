<?php

namespace CB\Bundle\NewAgeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use CB\Bundle\NewAgeBundle\Entity\Panel;
use CB\Bundle\NewAgeBundle\Entity\PanelAddress;

/**
 * @Route("/panel_address")
 */
class PanelAddressController extends Controller
{
    /**
     * @Route("/address-book/{id}", name="cb_newage_panel_address_book", requirements={"id"="\d+"})
     * @Template()
     * @AclAncestor("cb_newage_panel_address_view")
     */
    public function addressBookAction(Panel $panel)
    {
        return array(
            'entity' => $panel,
            'address_edit_acl_resource' => 'cb_newage_panel_address_update'
        );
    }

    /**
     * @Route(
     *      "/{panelId}/address-create",
     *      name="cb_newage_panel_address_create",
     *      requirements={"panelId"="\d+"}
     * )
     * @Template("CBNewAgeBundle:PanelAddress:update.html.twig")
     * @AclAncestor("cb_newage_panel_create")
     * @ParamConverter("panel", options={"id" = "panelId"})
     */
    public function createAction(Panel $panel)
    {
        return $this->update($panel, new PanelAddress());
    }

    /**
     * @Route(
     *      "/{panelId}/address-update/{id}",
     *      name="cb_newage_panel_address_update",
     *      requirements={"panelId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template
     * @AclAncestor("cb_newage_panel_update")
     * @ParamConverter("panel", options={"id" = "panelId"})
     */
    public function updateAction(Panel $panel, PanelAddress $address)
    {
        return $this->update($panel, $address);
    }

    /**
     * @param Panel $panel
     * @param PanelAddress $address
     * @return array
     * @throws BadRequestHttpException
     */
    protected function update(Panel $panel, PanelAddress $address)
    {
        $responseData = array(
            'saved' => false,
            'panel' => $panel
        );

        if ($this->getRequest()->getMethod() == 'GET' && !$address->getId()) {
//            $address->setFirstName($panel->getFirstName());
//            $address->setLastName($panel->getLastName());
            if (!$panel->getAddresses()->count()) {
                $address->setPrimary(true);
            }
        }

        if ($address->getOwner() && $address->getOwner()->getId() != $panel->getId()) {
            throw new BadRequestHttpException('Address must belong to panel');
        } elseif (!$address->getOwner()) {
            $panel->addAddress($address);
        }

        // Update panel's modification date when an address is changed
//        $panel->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));


        if ($this->get('cb_newage.form.handler.panel_address')->process($address)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $this->get('cb_newage.panel_address.form')->createView();
        return $responseData;
    }
}