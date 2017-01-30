<?php

namespace CB\Bundle\SchedulerBundle\Controller\Api\Rest;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;


use CB\Bundle\NewAgeBundle\Entity\Reservation;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerEventRepository;

use Doctrine\ORM\EntityNotFoundException;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
/**
 * @RouteResource("schedulerevent")
 * @NamePrefix("cb_api_")
 */
class SchedulerEventController extends RestController implements ClassResourceInterface
{
    /**
     * Get scheduler events.
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @QueryParam(
     *      name="start",
     *      requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *      nullable=true,
     *      strict=true,
     *      description="Start date in RFC 3339. For example: 2009-11-05T13:15:30Z."
     * )
     * @QueryParam(
     *      name="end",
     *      requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *      nullable=true,
     *      strict=true,
     *      description="End date in RFC 3339. For example: 2009-11-05T13:15:30Z."
     * )
     * @QueryParam(
     *      name="panelView",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Panel View id."
     * )
     * @QueryParam(
     *     name="createdAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="updatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @ApiDoc(
     *      description="Get scheduler events",
     *      resource=true
     * )
     * @AclAncestor("cb_scheduler_event_view")
     *
     * @return Response
     */
    public function cgetAction()
    {
//        $extendFields = $this->getExtendFieldNames('CB\Bundle\SchedulerBundle\Entity\SchedulerEvent');
//        $qb           = null;
//        if ($this->getRequest()->get('start') && $this->getRequest()->get('end')) {
//            $result = $this->get('cb_scheduler.scheduler_manager')->getSchedulerEvents(
//                $this->get('oro_security.security_facade')->getOrganization()->getId(),
//                $this->getUser()->getId(),
//                $schedulerId,
//                new \DateTime($this->getRequest()->get('start')),
//                new \DateTime($this->getRequest()->get('end')),
//                $subordinate,
//                $extendFields
//            );
//        } elseif ($this->getRequest()->get('page') && $this->getRequest()->get('limit')) {
//            $dateParamFilter  = new HttpDateTimeParameterFilter();
//            $filterParameters = ['createdAt' => $dateParamFilter, 'updatedAt' => $dateParamFilter];
//            $filterCriteria   = $this->getFilterCriteria(['createdAt', 'updatedAt'], $filterParameters);
//
//            /** @var SchedulerEventRepository $repo */
//            $repo  = $this->getManager()->getRepository();
//            $qb    = $repo->getUserEventListQueryBuilder($filterCriteria, $extendFields);
//            $page  = (int)$this->getRequest()->get('page', 1);
//            $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);
//            $qb
//                ->andWhere('c.id = :schedulerId')
//                ->setParameter('schedulerId', $schedulerId);
//            $qb->setMaxResults($limit)
//                ->setFirstResult($page > 0 ? ($page - 1) * $limit : 0);
//
//            $result = $this->get('oro_scheduler.scheduler_event_normalizer.user')->getSchedulerEvents(
//                $schedulerId,
//                $qb->getQuery()
//            );
//
//            return $this->buildResponse($result, self::ACTION_LIST, ['result' => $result, 'query' => $qb]);
//        } else {
//            throw new BadRequestHttpException(
//                'Time interval ("start" and "end") or paging ("page" and "limit") parameters should be specified.'
//            );
//        }
//
//        return new Response(json_encode($result), Codes::HTTP_OK);

        /** @var SchedulerEventRepository $repo */
        $repo  = $this->getManager()->getRepository();
        $qb = $repo->getEventListQueryBuilder();
//        $page  = (int)$this->getRequest()->get('page', 1);
//        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);
//        $qb->setMaxResults($limit)
//            ->setFirstResult($page > 0 ? ($page - 1) * $limit : 0);

        $panelViewId  = (int)$this->getRequest()->get('panelView', 0);

        if ($panelViewId > 0)
            $qb->andWhere('pv.id=:panelViewId')
                ->setParameter('panelViewId', $panelViewId);

            $result = $qb->getQuery()->getResult();

            $events = [];
            foreach ($result as $row)
            {
                $item['id'] = $row['id'];
                $item['title'] = $row['campaignName'];
                $item['start'] = $row['start']->format('c');

                // For correct display in Js Scheduler.
                $endDate = $row['end'];
                $endDate->modify("+1 day");

                $item['end'] = $endDate->format('c');
                $item['resourceId'] = $row['panelViewId'];
                $item['resourceName'] = $row['panelName'] . ' ' . $row['panelViewName'];
                $item['panelView'] = $row['panelViewId'];
                $item['campaign'] = $row['campaignId'];
//                $item['offer'] = $row['offerId'];
                $item['panel'] = $row['panelId'];
                $item['supportType'] = $row['supportTypeId'];
                $item['lightingType'] = $row['lightingTypeId'];
                $item['city'] = $row['city'];
                $item['status'] = $row['status'];

                $item['editable'] = false;
                if ($this->get('oro_security.security_facade')->isGranted('cb_scheduler_event_update'))
                    if ($row['status']!=2 || $this->get('oro_security.security_facade')->isGranted('ROLE_AVAILABLE'))
                        $item['editable'] = true;

                $item['removable'] = false;
                if ($this->get('oro_security.security_facade')->isGranted('cb_scheduler_event_delete'))
                    if ($row['status']!=2 || $this->get('oro_security.security_facade')->isGranted('ROLE_AVAILABLE'))
                        $item['removable'] = true;

                $events[] = $item;
            }

            return $this->buildResponse($events, self::ACTION_LIST, ['result' => $result, 'query' => $qb]);

//        $page  = (int)$this->getRequest()->get('page', 1);
//        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

//        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Get scheduler event.
     *
     * @param int $id Scheduler event id
     *
     * @ApiDoc(
     *      description="Get scheduler event",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_event_view")
     *
     * @return Response
     */
    public function getAction($id)
    {
        /** @var SchedulerEvent|null $entity */
        $entity = $this->getManager()->find($id);

        $result = null;
        $code   = Codes::HTTP_NOT_FOUND;
        if ($entity) {
            $result = $this->get('oro_scheduler.scheduler_event_normalizer.user')
                ->getSchedulerEvent(
                    $entity,
                    null,
                    $this->getExtendFieldNames('CB\Bundle\SchedulerBundle\Entity\SchedulerEvent')
                );
            $code   = Codes::HTTP_OK;
        }

        return $this->buildResponse($result ? : '', self::ACTION_READ, ['result' => $result], $code);
    }

    /**
     * Get scheduler event supposing it is displayed in the specified scheduler.
     *
     * @param int $id      The id of a scheduler where an event is displayed
     * @param int $eventId Scheduler event id
     *
     * @Get(
     *      "/schedulers/{id}/events/{eventId}",
     *      requirements={"id"="\d+", "eventId"="\d+"}
     * )
     * @ApiDoc(
     *      description="Get scheduler event supposing it is displayed in the specified scheduler",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_event_view")
     *
     * @return Response
     */
    public function getBySchedulerAction($id, $eventId)
    {
        /** @var SchedulerEvent|null $entity */
        $entity = $this->getManager()->find($eventId);

        $result = null;
        $code   = Codes::HTTP_NOT_FOUND;
        if ($entity) {
            $result = $this->get('oro_scheduler.scheduler_event_normalizer.user')
                ->getSchedulerEvent($entity, (int)$id);
            $code   = Codes::HTTP_OK;
        }

        return $this->buildResponse($result ? : '', self::ACTION_READ, ['result' => $result], $code);
    }

    /**
     * Update scheduler event.
     *
     * @param int $id Scheduler event id
     *
     * @ApiDoc(
     *      description="Update scheduler event",
     *      resource=true
     * )
     * @AclAncestor("oro_scheduler_event_update")
     *
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new scheduler event.
     *
     * @Post("schedulerevents")
     * @ApiDoc(
     *      description="Create new scheduler event",
     *      resource=true
     * )
     * @AclAncestor("cb_scheduler_event_create")
     *
     * @return Response
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Remove scheduler event.
     *
     * @param int $id Scheduler event id
     *
     * @ApiDoc(
     *      description="Remove scheduler event",
     *      resource=true
     * )
     * @Acl(
     *      id="cb_scheduler_event_delete",
     *      type="entity",
     *      class="CBSchedulerBundle:SchedulerEvent",
     *      permission="DELETE",
     *      group_name=""
     * )
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $isProcessed = false;

        try {
            /** @var SchedulerEvent|null $entity */
            $schedulerEvent = $this->getManager()->find($id);

            if (!$schedulerEvent) {
                throw new EntityNotFoundException();
            }

//            /** @var Reservation $reservation */
//            $reservation = $schedulerEvent->getReservation();
//            $reservation->removeReservedPanelView($schedulerEvent->getPanelView());

            $em = $this->getManager()->getObjectManager();
            $em->remove($schedulerEvent);
            $em->flush();

            $isProcessed = true;
            $view        = $this->view(null, Codes::HTTP_NO_CONTENT);
        } catch (EntityNotFoundException $notFoundEx) {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        } catch (ForbiddenException $forbiddenEx) {
            $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $id, 'success' => $isProcessed]);
    }

    /**
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('cb_scheduler.scheduler_event.manager.api');
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->get('cb_scheduler.scheduler_event.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('cb_scheduler.scheduler_event.form.handler.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        parent::fixFormData($data, $entity);

        if (isset($data['allDay']) && ($data['allDay'] === 'false' || $data['allDay'] === '0')) {
            $data['allDay'] = false;
        }

        // remove auxiliary attributes if any
        unset($data['updatedAt']);
        unset($data['editable']);
        unset($data['removable']);
        unset($data['notifiable']);

        return true;
    }

    /**
     * @return SystemSchedulerConfig
     */
    protected function getSchedulerConfig()
    {
        return $this->get('oro_scheduler.system_scheduler_config');
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdateRequest($id)
    {
        /** @var SchedulerEvent $entity */
        $entity = $this->getManager()->find($id);

        if ($entity) {
            try {
                $entity = $this->processForm($entity);
                if ($entity) {
                    $view = $this->view(null, Codes::HTTP_NO_CONTENT);
                } else {
                    $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
                }
            } catch (ForbiddenException $forbiddenEx) {
                $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
            }
        } else {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $id, 'entity' => $entity]);
    }

    /**
     * {@inheritdoc}
     */
    public function handleCreateRequest($_ = null)
    {
        $isProcessed = false;

        $entity = call_user_func_array([$this, 'createEntity'], func_get_args());
        try {
            $entity = $this->processForm($entity);
            if ($entity) {
                $view        = $this->view($this->createResponseData($entity), Codes::HTTP_CREATED);
                $isProcessed = true;
            } else {
                $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
            }
        } catch (ForbiddenException $forbiddenEx) {
            $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
        }

        return $this->buildResponse($view, self::ACTION_CREATE, ['success' => $isProcessed, 'entity' => $entity]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDeleteHandler()
    {
        return $this->get('cb_scheduler.scheduler_event.handler.delete');
    }

    /**
     * @param string $class
     *
     * @return array
     */
    protected function getExtendFieldNames($class)
    {
        $configProvider = $this->get('oro_entity_config.provider.extend');
        $configs        = $configProvider->filter(
            function (ConfigInterface $extendConfig) {
                return
                    $extendConfig->is('owner', ExtendScope::OWNER_CUSTOM) &&
                    ExtendHelper::isFieldAccessible($extendConfig) &&
                    !$extendConfig->has('target_entity') &&
                    !$extendConfig->is('is_serialized');
            },
            $class
        );

        return array_map(
            function (ConfigInterface $config) {
                return $config->getId()->getFieldName();
            },
            $configs
        );
    }
}
