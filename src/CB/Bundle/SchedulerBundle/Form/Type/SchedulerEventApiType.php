<?php

namespace CB\Bundle\SchedulerBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'integer',
                [
                    'required' => true,
                    'mapped'   => false
                ]
            )
            ->add(
                'campaign',
                'integer',
                [
                    'required' => true,
                    'mapped'   => false
                ]
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
                'date',
                [
                    'required'       => true,
                    'widget'         => 'single_text',
                    'format'         => DateTimeType::HTML5_FORMAT,
                    'model_timezone' => 'UTC'
                ]
            )
            ->add(
                'status',
                'integer',
                [
                    'required' => true,
                    'mapped'   => false
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
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmitData']);
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
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
            )
        );
    }

    /**
     * POST_SUBMIT event handler
     *
     * @param FormEvent $event
     */
    public function postSubmitData(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var SchedulerEvent $data */
        $data = $form->getData();
        if (empty($data)) {
            return;
        }

        $campaignId = $form->get('campaign')->getData();
        if (empty($campaignId)) {
            return;
        }

        $panelViewId = $form->get('panelView')->getData();
        if (empty($panelViewId)) {
            return;
        }

        $status = (int) $form->get('status')->getData();
        if (is_null($status)) {
            return;
        }

        $this->schedulerEventManager->setCampaign($data, (int)$campaignId);
        $this->schedulerEventManager->setPanelView($data, (int)$panelViewId);
        $this->schedulerEventManager->setStatus($data, (int)$status);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_scheduler_event_api';
    }
}
