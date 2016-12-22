<?php
namespace CB\Bundle\NewAgeBundle\Form\Handler;

use CB\Bundle\NewAgeBundle\Entity\Reservation;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;

class ReservationHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     *
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $entityManager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $entityManager)
    {
        $this->form = $form;
        $this->request = $request;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Process form
     *
     * @param mixed $entity
     *
     * @return mixed|null The instance of saved entity on successful processing; otherwise, null
     */
    public function process($entity)
    {
        $entity = $this->prepareFormData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($this->request);
            if ($this->form->isValid()) {
                return $this->onSuccess($entity) ?: $entity;
            }
        }

        return null;
    }

    /**
     * @param mixed $entity
     *
     * @return mixed The instance of form data object
     */
    protected function prepareFormData($entity)
    {
        $this->form->setData($entity);

        return $entity;
    }

    /**
     * "Success" form handler
     *
     * @param Reservation $entity
     *
     * @return mixed|null The instance of saved entity. Can be null if it is equal of the $entity argument
     */
    protected function onSuccess($entity)
    {
        $this->entityManager->persist($entity);

        // Add/Create coresponding events in scheduler.
        foreach ($entity->getReservedPanelViews() as $reservedPanelView)
        {
            if (!$entity->findEventBy($entity->getAttributes($reservedPanelView))) {
                $event = new SchedulerEvent();
                $event->setPanelView($reservedPanelView);
                $event->setStart($entity->getOffer()->getStart());
                $event->setEnd($entity->getOffer()->getEnd());
                $event->setCampaign($entity->getOffer()->getCampaign());
                $event->setStatus(SchedulerEvent::RESERVED);
                $event->setReservation($entity);

                $this->entityManager->persist($event);
                $entity->addEvent($event);
            }
        }

        // Remove events that has no coresponding Panel View anymore
        $events = $this->entityManager->getRepository('CBSchedulerBundle:SchedulerEvent')->findBy(
            [
                'reservation'   => $entity->getId()
            ]
        );
        foreach ($events as $event)
        {
            /** @var SchedulerEvent $event */
            if (!$entity->hasReservedPanelView($event->getPanelView()))
            {
                $this->entityManager->remove($event);
            }
        }

        $this->entityManager->flush();
    }
}