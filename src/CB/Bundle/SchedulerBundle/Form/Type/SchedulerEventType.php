<?php

namespace CB\Bundle\SchedulerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

class SchedulerEventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'start',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'cb.scheduler.scheduler_event.start.label',
                    'attr'     => ['class' => 'start'],
                ]
            )
            ->add(
                'end',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'cb.scheduler.scheduler_event.end.label',
                    'attr'     => ['class' => 'end'],
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    /**
     * @param SchedulerEvent $schedulerEvent
     * @param string        $status
     */
    protected function setDefaultEventStatus(SchedulerEvent $schedulerEvent, $status = SchedulerEvent::OFFERED)
    {
        if (!$schedulerEvent->getStatus()) {
            $schedulerEvent->setStatus($status);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'            => 'CB\Bundle\SchedulerBundle\Entity\SchedulerEvent',
                'intention'             => 'scheduler_event',
                'cascade_validation'    => true
            ]
        );
    }

    /**
     * PRE_SET_DATA event handler
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form   = $event->getForm();

        /** @var SchedulerEvent $data */
        $data = $event->getData();
        $form->add(
            $form->getConfig()->getFormFactory()->createNamed(
                'campaign',
                'cb_campaign_choice',
                $data ? $data->getCampaign() : null,
                [
                    'required'        => true,
                    'mapped'          => false,
                    'auto_initialize' => false,
                    'label'           => 'cb.newage.campaign.entity_label'
                ]
            )
        );
        $form->add(
            $form->getConfig()->getFormFactory()->createNamed(
                'panelView',
                'cb_panel_view_choice',
                $data ? $data->getCampaign() : null,
                [
                    'required'        => false,
                    'mapped'          => false,
                    'auto_initialize' => false,
                    'label'           => 'cb.newage.panel_view.entity_label'
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_scheduler_event';
    }
}
