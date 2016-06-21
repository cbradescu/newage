<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 14:05
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Panel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/panel")
 */
class PanelController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_panel_index")
     * @Template()
     * @AclAncestor("cb_newage_panel_view")
     */
    public function indexAction()
    {
        return array(
            'entity_class' => $this->container->getParameter('cb_newage_panel.entity.class')
        );
    }

    /**
     * @Route("/view/{id}", name="cb_newage_panel_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_panel_view")
     */
    public function viewAction(Panel $panel)
    {
        return [
            'entity' => $panel
        ];
    }

    /**
     * @Route("/create", name="cb_newage_panel_create")
     * @AclAncestor("cb_newage_panel_create")
     * @Template("CBNewAgeBundle:Panel:update.html.twig")
     */
    public function createAction()
    {
        $panel = $this->get('cb_newage_panel.manager')->createPanel();

        return $this->update($panel);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_panel_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_panel_update")
     */
    public function updateAction(Panel $panel)
    {
        return $this->update($panel);
    }

    /**
     * @param Panel $panel
     * @return array
     */
    protected function update(Panel $panel)
    {
        if ($this->get('cb_newage_panel.form.handler.entity')->process($panel)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.panel.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_panel_update', 'parameters' => ['id' => $panel->getId()]],
                ['route' => 'cb_newage_panel_view', 'parameters' => ['id' => $panel->getId()]]
            );
        }

        return array(
            'entity' => $panel,
            'form' => $this->get('cb_newage_panel.form.entity')->createView()
        );
    }
}