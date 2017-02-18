<?php

namespace CB\Bundle\SchedulerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

/**
 * @Route("/scheduler_event")
 */
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

    /**
     * @Route("/index", name="cb_scheduler_scheduler_event_index")
     * @Template()
     * @AclAncestor("cb_scheduler_event_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_scheduler_scheduler_event_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_scheduler_event_view")
     */
    public function viewAction(SchedulerEvent $schedulerEvent)
    {
        return [
            'entity' => $schedulerEvent
        ];
    }

    /**
     * @Route("/update/{id}", name="cb_scheduler_scheduler_event_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_scheduler_scheduler_event_update")
     */
    public function updateAction(SchedulerEvent $schedulerEvent)
    {
        return $this->update($schedulerEvent);
    }

    /**
     * @param SchedulerEvent $schedulerEvent
     * @return array
     */
    protected function update(SchedulerEvent $schedulerEvent)
    {
        if ($this->get('cb_scheduler.scheduler_event.form.handler')->process($schedulerEvent)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.scheduler.scheduler_event.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_scheduler_scheduler_event_update', 'parameters' => ['id' => $schedulerEvent->getId()]],
                ['route' => 'cb_scheduler_scheduler_event_view', 'parameters' => ['id' => $schedulerEvent->getId()]]
            );
        }

        return array(
            'entity' => $schedulerEvent,
            'form' => $this->get('cb_scheduler.scheduler_event.form')->createView()
        );
    }
}