<?php

namespace CB\Bundle\NewAgeBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\TypedAddressType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PanelAddressType extends TypedAddressType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CB\Bundle\NewAgeBundle\Entity\PanelAddress',
                'cascade_validation' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_typed_address';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_panel_address';
    }
}
