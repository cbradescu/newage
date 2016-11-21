<?php
namespace CB\Bundle\NewAgeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Router;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use Doctrine\Common\Collections\Collection;

class PanelViewMultipleEntityType extends AbstractType
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router             = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $panelViewCollection = $form->getData();
        $view->vars['initial_elements'] = $this->getInitialElements($panelViewCollection);
    }

    /**
     * @param Collection $panelViews
     * @return array
     */
    protected function getInitialElements(Collection $panelViews)
    {
        $result = array();

        /** @var PanelView $panelView */
        foreach ($panelViews as $panelView) {
            if (!$panelView->getId()) {
                continue;
            }
            $result[] = array(
                'id' => $panelView->getId(),
                'label' => $panelView->getPanel()->getName() . ' ' . $panelView->getName(),
                'link' => $this->router->generate(
                    'cb_offer_widget_panel_views_info',
                    array('id' => $panelView->getId())
                ),
                'extraData' => array(
                    array('label' => 'City', 'value' => $panelView->getCity() ?: 'N/A'),
                    array('label' => 'Support type', 'value' => $panelView->getSupport() ?: 'N/A'),
                    array('label' => 'Lighting type', 'value' => $panelView->getLighting() ?: 'N/A'),
                ),
                'isDefault' => false
            );
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_multiple_entity';
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cb_panel_view_multiple_entity';
    }
}