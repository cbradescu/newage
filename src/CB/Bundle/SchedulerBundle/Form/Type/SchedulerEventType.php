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
                'panelView',
                'entity',
                array(
                    'label'       => 'cb.newage.panel_view.entity_label',
                    'class'       => 'CBNewAgeBundle:PanelView',
                    'property'    => 'name',
                    'empty_value' => 'cb.newage.panel_view.form.choose_panel_view'
                )
            )
            ->add(
                'campaign',
                'entity',
                array(
                    'label'       => 'cb.newage.campaign.entity_label',
                    'class'       => 'CBNewAgeBundle:Campaign',
                    'property'    => 'title',
                    'empty_value' => 'cb.newage.campaign.form.choose_campaign'
                )
            )
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
            );

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
                'cascade_validation' => true,
                'ownership_disabled' => true
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
