<?php

namespace CB\Bundle\SchedulerBundle\Controller\Api\Rest;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\CalendarBundle\Manager\CalendarPropertyApiEntityManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

/**
 * @NamePrefix("cb_api_")
 */
class SchedulerConnectionController extends RestController implements ClassResourceInterface
{
    /**
     * Get scheduler connections.
     *
     * @param int $id User's scheduler id
     *
     * @Get("/schedulers/{id}/connections", requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Get scheduler connections",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_view")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function cgetAction($id)
    {
        $items = $this->getManager()->getSchedulerManager()
            ->getSchedulers(
                $this->get('oro_security.security_facade')->getOrganization()->getId(),
                $this->getUser()->getId(),
                $id
            );

        return new Response(json_encode($items), Codes::HTTP_OK);
    }

    /**
     * Update scheduler connection.
     *
     * @param int $id Calendar connection id
     *
     * @Put("/schedulerconnections/{id}", requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Update scheduler connection",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_view")
     *
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new scheduler connection.
     *
     * @Post("/schedulerconnections")
     * @ApiDoc(
     *      description="Create new scheduler connection",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_view")
     *
     * @return Response
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Remove scheduler connection.
     *
     * @param int $id Calendar connection id
     *
     * @Delete("/schedulerconnections/{id}", requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Remove scheduler connection",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_view")
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return CalendarPropertyApiEntityManager
     */
    public function getManager()
    {
        return $this->get('oro_scheduler.scheduler_property.manager.api');
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->get('oro_scheduler.scheduler_property.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_scheduler.scheduler_property.form.handler.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        parent::fixFormData($data, $entity);

        unset(
            $data['schedulerName'],
            $data['removable'],
            $data['canAddEvent'],
            $data['canEditEvent'],
            $data['canDeleteEvent']
        );

        return true;
    }
}
