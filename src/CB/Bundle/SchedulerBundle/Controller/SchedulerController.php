<?php

namespace CB\Bundle\SchedulerBundle\Controller;

use CB\Bundle\SchedulerBundle\CBSchedulerBundle;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Oro\Bundle\SecurityBundle\SecurityFacade;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

//use Oro\Bundle\UserBundle\Entity\User;
//use Oro\Bundle\OrganizationBundle\Entity\Organization;
//use CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerRepository;

//use CB\Bundle\NewAgeBundle\Entity\PanelView;
use Oro\Bundle\CalendarBundle\Provider\CalendarDateTimeConfigProvider;


class SchedulerController extends Controller
{
    /**
     * @Route("/view", name="cb_scheduler_view")
     * @Template()
     * @AclAncestor("cb_scheduler_view")
     */
    public function viewAction()
    {
        // !!! Este nevoie de o entitate pentru view.
        $entity = new SchedulerEvent();

        /** @var CalendarDateTimeConfigProvider $schedulerConfigProvider */
        $schedulerConfigProvider = $this->get('oro_calendar.provider.calendar_config');

        $dateRange = $schedulerConfigProvider->getDateRange();

        return [
            'entity' => $entity,
            'event_form' => $this->get('cb_scheduler.scheduler_event.form')->createView(),
//            'entity' => $scheduler,
            'scheduler' => array(
                'selectable' => true,
                'editable' => true,
                'removable' => true
//                'selectable' => $securityFacade->isGranted('cb_scheduler_event_create'),
//                'editable' => $securityFacade->isGranted('cb_scheduler_event_update'),
//                'removable' => $securityFacade->isGranted('cb_scheduler_event_delete')
//                'timezoneOffset' => $schedulerConfigProvider->getTimezoneOffset()
            ),
            'startDate' => $dateRange['startDate'],
            'endDate' => $dateRange['endDate'],
        ];
    }
}