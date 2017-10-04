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
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.scheduler.scheduler_event.start.label',
                    'attr'     => ['class' => 'start'],
                ]
            )
            ->add(
                'end',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.scheduler.scheduler_event.end.label',
                    'attr'     => ['class' => 'end'],
                ]
            )
            ->add(
                'status',
                'choice',
                array(
                    'label'       => 'cb.scheduler.scheduler_event.status.label',
                    'choices'     => [
                        0 => 'Reserved',
                        1 => 'Confirmed'
                    ],
                    'expanded' => false,
                    'translatable_options' => false
                )
            )
        ;
    }

    /**
     * @param SchedulerEvent $schedulerEvent
     * @param string        $status
     */
    protected function setDefaultEventStatus(SchedulerEvent $schedulerEvent, $status = SchedulerEvent::RESERVED)
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_scheduler_event';
    }
}
