<?php

namespace CB\Bundle\NewAgeBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use CB\Bundle\NewAgeBundle\Entity\Panel;
use CB\Bundle\NewAgeBundle\Entity\PanelAddress;

/**
 * @RouteResource("address")
 * @NamePrefix("cb_newage_panel_address_api_")
 */
class PanelAddressController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET address
     *
     * @param string $panelId
     * @param string $addressId
     *
     * @ApiDoc(
     *      description="Get panel address",
     *      resource=true
     * )
     * @AclAncestor("cb_newage_panel_address_view")
     * @return Response
     */
    public function getAction($panelId, $addressId)
    {
        /** @var Panel $panel */
        $panel = $this->getPanelManager()->find($panelId);

        /** @var PanelAddress $address */
        $address = $this->getManager()->find($addressId);

        $addressData = null;
        if ($address && $panel->getAddresses()->contains($address)) {
            $addressData = $this->getPreparedItem($address);
        }
        $responseData = $addressData ? json_encode($addressData) : '';
        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("cb_newage_panel_address_view")
     * @param int $panelId
     *
     * @return JsonResponse
     */
    public function cgetAction($panelId)
    {
        /** @var Panel $panel */
        $panel = $this->getPanelManager()->find($panelId);
        $result  = [];

        if (!empty($panel)) {
            $items = $panel->getAddresses();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($panel) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("cb_newage_panel_address_delete")
     * @param     $panelId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction($panelId, $addressId)
    {
        /** @var PanelAddress $address */
        $address = $this->getManager()->find($addressId);

        /** @var Panel $panel */
        $panel = $this->getPanelManager()->find($panelId);
        if ($panel->getAddresses()->contains($address)) {
            $panel->removeAddress($address);
            return $this->handleDeleteRequest($addressId);
        } else {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }
    }

    /**
     * REST GET address by type
     *
     * @param string $panelId
     * @param string $typeName
     *
     * @ApiDoc(
     *      description="Get panel address by type",
     *      resource=true
     * )
     * @AclAncestor("cb_newage_panel_address_view")
     * @return Response
     */
    public function getByTypeAction($panelId, $typeName)
    {
        /** @var Panel $panel */
        $panel = $this->getPanelManager()->find($panelId);

        if ($panel) {
            $address = $panel->getAddressByTypeName($typeName);
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * REST GET primary address
     *
     * @param string $panelId
     *
     * @ApiDoc(
     *      description="Get panel primary address",
     *      resource=true
     * )
     * @AclAncestor("cb_newage_panel_address_view")
     * @return Response
     */
    public function getPrimaryAction($panelId)
    {
        /** @var Panel $panel */
        $panel = $this->getPanelManager()->find($panelId);

        if ($panel) {
            $address = $panel->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    protected function getPanelManager()
    {
        return $this->get('cb_newage_panel.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('cb_newage_panel_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        // convert addresses to plain array
        $addressTypesData = array();

        /** @var $entity PanelAddress */
        foreach ($entity->getTypes() as $addressType) {
            $addressTypesData[] = parent::getPreparedItem($addressType);
        }

        $result                = parent::getPreparedItem($entity);
        $result['types']       = $addressTypesData;
        $result['countryIso2'] = $entity->getCountryIso2();
        $result['countryIso3'] = $entity->getCountryIso2();
        $result['regionCode']  = $entity->getRegionCode();
        $result['country']     = $entity->getCountryName();
        $result['latitude']    = $entity->getLatitude();
        $result['longitude']   = $entity->getLongitude();

        unset($result['owner']);

        return $result;
    }
}