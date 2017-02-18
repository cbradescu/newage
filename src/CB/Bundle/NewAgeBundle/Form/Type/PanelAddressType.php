<?php

namespace CB\Bundle\NewAgeBundle\Form\Type;

use CB\Bundle\NewAgeBundle\Form\EventListener\FixPanelAddressesPrimarySubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanelAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['single_form'] && $options['all_addresses_property_path']) {
            $builder->addEventSubscriber(
                new FixPanelAddressesPrimarySubscriber($options['all_addresses_property_path'])
            );
        }

        $builder
            ->add('id', 'hidden')
            ->add(
                'primary',
                'checkbox',
                array(
                    'required' => false
                )
            )
            ->add('street', 'text', array('required' => true, 'label' => 'oro.address.street.label'))
            ->add('street2', 'text', array('required' => false, 'label' => 'oro.address.street2.label'))
            ->add(
                'city',
                'entity',
                [
                    'label' => 'oro.address.city.label',
                    'class'       => 'CBNewAgeBundle:City',
                    'property'    => 'name',
                    'empty_value' => 'cb.newage.city.form.choose_city',
                    'required' => true
                ]
            )
            ->add(
                'latitude',
                'text',
                [
                    'label' => 'cb.newage.panel.address.latitude.label',
                    'required' => true
                ]
            )
            ->add(
                'longitude',
                'text',
                [
                    'label' => 'cb.newage.panel.address.longitude.label',
                    'required' => true
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CB\Bundle\NewAgeBundle\Entity\PanelAddress',
                'intention' => 'address',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'all_addresses_property_path' => 'owner.addresses',
                'single_form' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_panel_address';
    }
}
