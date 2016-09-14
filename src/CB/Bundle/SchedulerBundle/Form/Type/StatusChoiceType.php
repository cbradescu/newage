<?php

namespace CB\Bundle\SchedulerBundle\Form\Type;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use CB\Bundle\SchedulerBundle\Manager\SchedulerEventManager;

class StatusChoiceType extends AbstractType
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface  $translator
     */
    public function __construct(SecurityFacade $securityFacade, TranslatorInterface $translator)
    {
        $this->securityFacade       = $securityFacade;
        $this->translator           = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmitData']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices'              => function (Options $options) {
                    return $this->getChoices();
                },
                'expanded' => false,
                'translatable_options' => false
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

        $data = $form->getData();
        if (empty($data)) {
            return;
        }
    }

    /**
     * @return array key , value
     */
    protected function getChoices()
    {
        $choices[0] = $this->translator->trans('cb.scheduler.scheduler_event.status.offered.label');
        if ($this->securityFacade->isGranted('cb_scheduler_event_update')) {
            $choices[1] = $this->translator->trans('cb.scheduler.scheduler_event.status.reserved.label');

            if ($this->securityFacade->isGranted('ROLE_AVAILABLE'))
            {
                $choices[2] = $this->translator->trans('cb.scheduler.scheduler_event.status.accepted.label');
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_status_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }
}
