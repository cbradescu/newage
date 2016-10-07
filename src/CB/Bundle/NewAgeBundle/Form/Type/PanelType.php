<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 14:05
 */

namespace CB\Bundle\NewAgeBundle\Form\Type;

use CB\Bundle\NewAgeBundle\Entity\Panel;
use CB\Bundle\NewAgeBundle\Entity\PanelType as PanelTypeEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;

class PanelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'label' => 'cb.newage.panel.code.label',
                    'required' => true
                ]
            )
            ->add(
                'supportType',
                'entity',
                array(
                    'label'       => 'cb.newage.supporttype.entity_label',
                    'class'       => 'CBNewAgeBundle:SupportType',
                    'property'    => 'name',
                    'empty_value' => 'cb.newage.supporttype.form.choose_support_type'
                )
            )
            ->add(
                'lighting',
                'checkbox',
                array(
                    'label' => 'cb.newage.panel.lighting.label',
                    'required' => false
                )
            )
            ->add(
                'dimensions',
                'text',
                [
                    'label' => 'cb.newage.panel.dimensions.label',
                    'required' => false
                ]
            )
            ->add(
                'neighborhoods',
                'textarea',
                [
                    'label' => 'cb.newage.panel.neighborhoods.label',
                    'required' => false
                ]
            )
            ->add(
                'addresses',
                'oro_address_collection',
                array(
                    'label'    => '',
                    'type'     => 'cb_panel_address',
                    'required' => true,
                    'options'  => array('data_class' => 'CB\Bundle\NewAgeBundle\Entity\PanelAddress')
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\Panel',
                'intention' => 'cb_newage_panel_entity',
                'cascade_validation' => true,
                'ownership_disabled' => true
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'cb_newage_panel';
    }

}