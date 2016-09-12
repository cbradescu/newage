<?php

namespace CB\Bundle\SchedulerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use CB\Bundle\SchedulerBundle\Manager\SchedulerEventManager;

class PanelViewChoiceType extends AbstractType
{
    /** @var SchedulerEventManager */
    protected $schedulerEventManager;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param SchedulerEventManager $schedulerEventManager
     * @param TranslatorInterface  $translator
     */
    public function __construct(SchedulerEventManager $schedulerEventManager, TranslatorInterface $translator)
    {
        $this->schedulerEventManager = $schedulerEventManager;
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
                    return $this->getChoices($options['is_new']);
                },
                'is_new'               => false,
                'translatable_options' => false
            )
        );
        $resolver->setNormalizers(
            array(
                'expanded'    => function (Options $options, $expanded) {
                    return count($options['choices']) === 1;
                },
                'multiple'    => function (Options $options, $multiple) {
                    return count($options['choices']) === 1;
                },
                'empty_value' => function (Options $options, $emptyValue) {
                    return count($options['choices']) !== 1 ? null : null;
                },
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
        if (is_array($data)) {
            $data = reset($data);
        }

//        /** @var SchedulerEvent $parentData */
//        $parentData = $form->getParent()->getData();
//        if (!$parentData) {
//            return;
//        }
//
//        list($schedulerAlias, $schedulerId) = $this->schedulerEventManager->parseSchedulerUid($data);
//        $this->schedulerEventManager->setScheduler($parentData, $schedulerAlias, $schedulerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_panel_view_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @param bool $isNew
     *
     * @return array key = schedulerUid, value = scheduler name
     */
    protected function getChoices($isNew)
    {
        $panelViews = $this->schedulerEventManager->getPanelViews();
        if ($isNew && count($panelViews) === 1) {
            $panelViews[0]['name'] = $this->translator->trans(
                'oro.scheduler.add_to_scheduler',
                ['%name%' => $panelViews[0]['name']]
            );
        } elseif (!$isNew || count($panelViews) !== 0) {
            usort(
                $panelViews,
                function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                }
            );
        }

        $choices = [];
        foreach ($panelViews as $panelView) {
            $choices[$panelView['id']] = $panelView['name'];
        }

        return $choices;
    }
}
