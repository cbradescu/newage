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

class ClientChoiceType extends AbstractType
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
                    return $this->getChoices();
                },
                'expanded' => false,
                'translatable_options' => false
            )
        );
        $resolver->setNormalizers(
            array(
                'empty_value' => function (Options $options) {
                    return count($options['choices']) !== 1 ? 'cb.newage.client.form.choose_client' : null;
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
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_client_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @return array key = clientId, value = clientName
     */
    protected function getChoices()
    {
        $clients = $this->schedulerEventManager->getClients();
            usort(
                $clients,
                function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                }
            );

        $choices = [];
        foreach ($clients as $client) {
            $choices[$client['id']] = $client['name'];
        }

        return $choices;
    }
}
