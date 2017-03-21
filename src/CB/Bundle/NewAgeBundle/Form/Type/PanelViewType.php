<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 11:22
 */

namespace CB\Bundle\NewAgeBundle\Form\Type;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\PanelViewType as PanelViewTypeEntity;
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

class PanelViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'panel',
                'entity',
                array(
                    'label'       => 'cb.newage.panel.entity_label',
                    'class'       => 'CBNewAgeBundle:Panel',
                    'property'    => 'name',
                    'empty_value' => 'cb.newage.panelview.form.choose_panel_view'
                )
            )
            ->add(
                'name',
                'text',
                [
                    'label' => 'cb.newage.panelview.name.label',
                    'required' => true
                ]
            )
            ->add(
                'sketch',
                'text',
                [
                    'label' => 'cb.newage.panelview.sketch.label',
                    'required' => false
                ]
            )
            ->add(
                'url',
                'text',
                [
                    'label' => 'cb.newage.panelview.url.label',
                    'required' => false
                ]
            )
            ->add(
                'poster',
                'text',
                [
                    'label' => 'cb.newage.panelview.poster.label',
                    'required' => false
                ]
            )
            ->add('price', 'oro_money', ['required' => false]);
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\PanelView',
                'intention' => 'cb_newage_panel_view_entity',
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
        return 'cb_newage_panel_view';
    }

}