<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 21/Jun/16
 * Time: 15:10
 */

namespace CB\Bundle\NewAgeBundle\Form\Type;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Campaign;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;

class CampaignType extends AbstractType
{
    var $router;

    /**
     * @param mixed $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @param Router $router
     * @param SecurityFacade $securityFacade
     */
    public function __construct(Router $router, SecurityFacade $securityFacade)
    {
        $this->setRouter($router);
        $this->securityFacade = $securityFacade;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                'text',
                [
                    'required' => true,
                    'label'    => 'cb.newage.campaign.title.label'
                ]
            )
            ->add(
                'description',
                'oro_resizeable_rich_text',
                [
                    'required' => false,
                    'label'    => 'cb.newage.campaign.description.label'
                ]
            )
            ->add(
                'start',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.newage.campaign.start.label',
                    'attr'     => ['class' => 'start'],
                ]
            )
            ->add(
                'end',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.newage.campaign.end.label',
                    'attr'     => ['class' => 'end'],
                ]
            )
            ->add(
                'confirmed',
                'checkbox',
                array(
                    'label' => 'cb.newage.campaign.confirmed.label',
                    'disabled'  => !$this->securityFacade->isGranted('ROLE_AVAILABLE'),
                    'required' => false
                )
            )
            ->add(
                'panelViews',
                'oro_multiple_entity',
                [
                    'required' => false,
                    'block' => 'PanelView',
                    'block_config' => array(

                        'PanelView' => array(
                            'title' => '',
                            'subblocks' => array(
                                array(
                                    'useSpan' => ''
                                )
                            )
                        )
                    ),
                    'label' => 'cb.newage.panel_view.entity_plural_label',
                    'class' => 'CB\Bundle\NewAgeBundle\Entity\PanelView',
                    'grid_url' => $this->router->generate(
                        'oro_entity_relation',
                        [
                            'id' => 0,
                            'entityName' => 'CB_Bundle_NewAgeBundle_Entity_PanelView',
                            'fieldName' => 'panelViews'
                        ]
                    ),
                    'default_element' => 'cb_newage_form_panelViews',
                    'selector_window_title' => 'Select Panel Views',
                    'initial_elements' => null,
                    //'extend' => true,
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
            [
                'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\Campaign',
                'intention' => 'cb_newage_campaign_entity',
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
        return 'cb_newage_campaign';
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var Campaign $campaign */
        $campaign = $form->getData();
        $view->children['panelViews']->vars['grid_url']
            = $this->router->generate('cb_newage_campaign_widget_panel_views_info', array('id' => $campaign->getId()));
        $view->children['panelViews']->vars['initial_elements']
            = $this->getInitialPanelViews($campaign->getPanelViews());
    }

    /**
     * @param Collection $panelViews
     * @return array
     */
    protected function getInitialPanelViews(Collection $panelViews)
    {
        $result = array();

        /** @var PanelView $panelView */
        foreach ($panelViews as $panelView) {
            $result[] = array(
                'id' => $panelView->getId(),
                'label' => $panelView->getName(),
                'link' => $this->router->generate('cb_newage_panel_view_info', array('id' => $panelView->getId())),
                'extraData' => array(
                    array('label' => 'Panel', 'value' => $panelView->getPanel()->getName() ?: 'N/A'),
                    array('label' => 'Dimensions', 'value' => $panelView->getPanel()->getDimensions() ?: 'N/A'),
                ),
                'isDefault' => false
            );
        }

        return $result;
    }
}