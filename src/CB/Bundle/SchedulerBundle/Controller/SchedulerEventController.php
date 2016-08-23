<?php

namespace CB\Bundle\SchedulerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;


class SchedulerEventController extends Controller
{

    /**
     * @Route("/event_add/{panelView}/{start}", name="cb_scheduler_event_add", requirements={"panelView"="\d+", "start"="\d{4}(-\d{2}(-\d{2}?)?)?"})
     * @AclAncestor("cb_scheduler_create")
     * @Template("CBSchedulerBundle:SchedulerEvent:update.html.twig")
     */
    public function addAction(PanelView $panelView, \DateTime $start)
    {
        $startMoment = new \DateTime($start->format('Y-m-d'));
        $startMoment->add( new \DateInterval('P1D'));

        $endMoment = new \DateTime($start->format('Y-m-d'));
        $endMoment->add( new \DateInterval('P2D'));


        /** @var SchedulerEvent $schedulerEvent */
        $schedulerEvent = $this->get('cb_scheduler.scheduler_event.manager')->createSchedulerEvent();

        $schedulerEvent->setPanelView($panelView);
        $schedulerEvent->setStart($startMoment);
        $schedulerEvent->setEnd($endMoment);

        if ($this->get('cb_scheduler.scheduler_event.form.handler')->process($schedulerEvent)) {
            return array(
                'saved' => true,
                'entity' => $schedulerEvent,
                'form' => $this->get('cb_scheduler.scheduler_event.form')->createView()
            );
        }

        return array(
            'entity' => $schedulerEvent,
            'form' => $this->get('cb_scheduler.scheduler_event.form')->createView()
        );
    }
}