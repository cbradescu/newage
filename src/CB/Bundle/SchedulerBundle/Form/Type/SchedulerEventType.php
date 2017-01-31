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
                'panelView',
                'entity',
                array(
                    'label'       => 'cb.newage.panelview.entity_label',
                    'class'       => 'CBNewAgeBundle:PanelView',
//                    'property'    => 'name',
                    'empty_value' => 'cb.newage.panelview.form.choose_panel_view'
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
                'status',
                'choice',
                array(
                    'label'       => 'cb.scheduler.scheduler_event.status.label',
                    'choices'     => [
                        1 => 'Reserved',
                        2 => 'Confirmed'
                    ],
                    'expanded' => false,
                    'translatable_options' => false
                )
            )
        ;

//        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
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
                $data ? $data->getPanelView() : null,
                [
                    'required'        => false,
                    'mapped'          => false,
                    'auto_initialize' => false,
                    'label'           => 'cb.newage.panel_view.entity_label'
                ]
            )
        );
        $form->add(
            $form->getConfig()->getFormFactory()->createNamed(
                'status',
                'cb_status_choice',
                $data ? $data->getStatus() : null,
                [
                    'required'        => true,
                    'mapped'          => false,
                    'auto_initialize' => false,
                    'label'           => 'cb.scheduler.scheduler_event.status.label'
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
