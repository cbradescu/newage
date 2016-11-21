<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Form\Type;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferType as OfferTypeEntity;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
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

class OfferType extends AbstractType
{
    /** @var Router  */
    var $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'required' => true,
                    'label'    => 'cb.newage.city.name.label'
                ]
            )
            ->add(
                'start',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.newage.offer.start.label',
                    'attr'     => ['class' => 'start'],
                ]
            )
            ->add(
                'end',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.newage.offer.end.label',
                    'attr'     => ['class' => 'end'],
                ]
            )
            ->add(
                'campaign',
                'entity',
                [
                    'label'       => 'cb.newage.campaign.entity_label',
                    'class'       => 'CBNewAgeBundle:Campaign',
                    'property'    => 'title',
                    'required' => true
                ]
            )
//            ->add(
//                'panelViews',
//                'cb_panel_view_multiple_entity',
//                array(
//                    'add_acl_resource'      => 'cb_newage_panel_view_view',
//                    'class'                 => 'CBNewAgeBundle:PanelView',
//                    'selector_window_title' => 'cb.newage.offer.form.select_panel_views',
//                    'required'              => true
//                )
//            )
        ;
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function buildView(FormView $view, FormInterface $form, array $options)
//    {
//        $view->children['panelViews']->vars['grid_url'] = $this->router->generate(
//            'oro_entity_relation',
//            [
//                'id' => 0,
//                'entityName' => 'CB_Bundle_NewAgeBundle_Entity_Offer',
//                'fieldName' => 'panelViews'
//            ]
//        );
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function finishView(FormView $view, FormInterface $form, array $options)
//    {
//        /** @var Offer $offer */
//        $offer = $form->getData();
//        $view->children['panelViews']->vars['grid_url'] = $this->router->generate(
//            'cb_offer_widget_panel_views_info',
//            array('id' => $offer->getId())
//        );
//        $view->children['panelViews']->vars['initial_elements']
//            = $this->getInitialPanelViews($offer->getPanelViews());
//    }
//
//    /**
//     * @param Collection $panelViews
//     * @return array
//     */
//    protected function getInitialPanelViews(Collection $panelViews)
//    {
//        $result = array();
//
//        /** @var PanelView $panelView */
//        foreach ($panelViews as $panelView) {
//            $result[] = array(
//                'id' => $panelView->getId(),
//                'label' => 'cb.newage.panel_view.entity_plural_label',
//                'link' => $this->router->generate(
//                    'cb_offer_widget_panel_views_info',
//                    array('id' => $panelView->getId())
//                ),
//                'extraData' => array(
//                    array('label' => 'Name', 'value' => $panelView->getName() ?: 'N/A'),
//                    array('label' => 'Panel', 'value' => $panelView->getPanel()->getName() ?: 'N/A'),
//                ),
//                'isDefault' => false
//            );
//        }
//        return $result;
//    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\Offer',
                'intention' => 'cb_newage_offer_entity',
                'cascade_validation' => true,
                'ownership_disabled' => true,
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
        return 'cb_newage_offer';
    }
}