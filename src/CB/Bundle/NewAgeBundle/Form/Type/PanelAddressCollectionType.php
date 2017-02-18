<?php

namespace CB\Bundle\NewAgeBundle\Form\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class PanelAddressCollectionType extends AbstractType
{
    const NAME = 'cb_panel_address_collection';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setNormalizers(
            array(
                'options' => function (Options $options, $values) {
                    if (!$values) {
                        $values = array();
                    }
                    $values['single_form'] = false;

                    return $values;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
