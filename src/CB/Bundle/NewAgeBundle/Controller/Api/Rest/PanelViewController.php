<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 11:22
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Rest;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use Oro\Bundle\SoapBundle\Request\Parameters\Filter\StringToArrayParameterFilter;

/**
 * @Rest\RouteResource("panel_view")
 * @Rest\NamePrefix("cb_newage_panel_view_api_")
 */
class PanelViewController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Page number, starting from 1. Defaults to 1."
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Number of items per page. defaults to 10."
     * )
     * @Rest\QueryParam(
     *      name="panel",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Panel id."
     * )
     * @Rest\QueryParam(
     *      name="id",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Id."
     * )
     * @QueryParam(
     *      name="supportType",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Support type id."
     * )
     * @QueryParam(
     *      name="lightingType",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Lighting type id."
     * )
     * @QueryParam(
     *      name="city",
     *      requirements=".+",
     *      nullable=true,
     *      description="City id. One or several city ids separated by comma. Defaults to all cities"
     * )
     * @ApiDoc(
     *     description="Get all PanelView items",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_panel_view_view")
     * @return Response
     */
    public function cgetAction()
    {
        /** @var PanelViewRepository $repo */
        $repo  = $this->getManager()->getRepository();
        $qb = $repo->getPanelViewsQueryBuilder($this->get('oro_security.security_facade')->getOrganization()->getId());
        $qb->setMaxResults(1000);

        $panelId  = (int)$this->getRequest()->get('panel', 0);
        $panelViewId  = (int)$this->getRequest()->get('id', 0);
        $supportTypeId  = (int)$this->getRequest()->get('supportType', 0);
        $lightingTypeId  = (int)$this->getRequest()->get('lightingType', 0);

        $city = $this->getRequest()->get('city', null);
        if ($city and $city!='All')
            $cities = explode(',', $city);
        else
            $cities = [];

        if ($panelId)
            $qb->andWhere('p.id=:panelId')
                ->setParameter('panelId', $panelId);
        if ($panelViewId)
            $qb->andWhere('c.id=:panelViewId')
                ->setParameter('panelViewId', $panelViewId);
        if ($supportTypeId)
            $qb->andWhere('p.supportType=:supportTypeId')
                ->setParameter('supportTypeId', $supportTypeId);
        if ($lightingTypeId)
            $qb->andWhere('p.lightingType=:lightingTypeId')
                ->setParameter('lightingTypeId', $lightingTypeId);
        if (count($cities)!=0)
            $qb->andWhere($qb->expr()->in('a.city', ':cities'))
                ->setParameter('cities', $cities);

        $result = $qb->getQuery()->getResult();

        $panelViews = [];
        foreach ($result as $row) {
            $item['id'] = $row['id'];
            $item['name'] = $row['panelName'] . ' ' .$row['name'];

            $panelViews[] = $item;
        }

        return $this->buildResponse($panelViews, self::ACTION_LIST, ['result' => $result, 'query' => $qb]);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *     description="Get PanelView item",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_panel_view_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id PanelView item id
     *
     * @ApiDoc(
     *     description="Update PanelView",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_panel_view_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new panel_view
     *
     * @ApiDoc(
     *     description="Create new PanelView",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_panel_view_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete PanelView",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_panel_view_delete")
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('cb_newage_panel_view.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('cb_newage_panel_view.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('cb_newage_panel_view.form.handler.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'owner':
                //TODO add here any other entity
            default:
                parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        /** @var PanelView $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);

        return true;
    }
}