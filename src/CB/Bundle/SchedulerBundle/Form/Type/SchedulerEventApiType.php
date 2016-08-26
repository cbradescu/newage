<?php

namespace CB\Bundle\SchedulerBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use CB\Bundle\SchedulerBundle\Manager\SchedulerEventManager;

class SchedulerEventApiType extends SchedulerEventType
{
    /** @var SchedulerEventManager */
    protected $schedulerEventManager;

    /**
     * @param SchedulerEventManager $schedulerEventManager
     */
    public function __construct(SchedulerEventManager $schedulerEventManager)
    {
        $this->schedulerEventManager = $schedulerEventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden', array('mapped' => false))
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
                'date',
                [
                    'required'       => true,
                    'widget'         => 'single_text',
                    'format'         => DateTimeType::HTML5_FORMAT,
                    'model_timezone' => 'UTC'
                ]
            )
            ->add(
                'end',
                'datetime',
                [
                    'required'       => true,
                    'widget'         => 'single_text',
                    'format'         => DateTimeType::HTML5_FORMAT,
                    'model_timezone' => 'UTC'
                ]
            )
            ->add(
                'createdAt',
                'datetime',
                [
                    'required'       => false,
                    'widget'         => 'single_text',
                    'format'         => DateTimeType::HTML5_FORMAT,
                    'model_timezone' => 'UTC'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'CB\Bundle\SchedulerBundle\Entity\SchedulerEvent',
                'intention'            => 'scheduler_event',
                'csrf_protection'      => false,
                'ownership_disable'    => true,
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_scheduler_event_api';
    }
}
