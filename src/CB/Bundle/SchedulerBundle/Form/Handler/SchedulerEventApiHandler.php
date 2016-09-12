<?php

namespace CB\Bundle\SchedulerBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

class SchedulerEventApiHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param FormInterface      $form
     * @param Request            $request
     * @param ObjectManager      $manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager
    ) {
        $this->form               = $form;
        $this->request            = $request;
        $this->manager            = $manager;
    }

    /**
     * Process form
     *
     * @param  SchedulerEvent $entity
     * @return bool  True on successful processing, false otherwise
     */
    public function process(SchedulerEvent $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess(
                    $entity
                );
                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param SchedulerEvent   $entity
     */
    protected function onSuccess(SchedulerEvent $entity) {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
