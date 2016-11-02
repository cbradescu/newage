<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 11:22
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/panel_view")
 */
class PanelViewController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_panel_view_index")
     * @Template()
     * @AclAncestor("cb_newage_panel_view_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_panel_view_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_panel_view_view")
     */
    public function viewAction(PanelView $panelView)
    {
        return [
            'entity' => $panelView
        ];
    }

    /**
     * @Route("/info/{id}", name="cb_newage_panel_view_info", requirements={"id"="\d+"})
     *
     * @Template
     * @AclAncestor("cb_newage_panel_view_view")
     */
    public function infoAction(PanelView $panelView)
    {
        if (!$this->getRequest()->get('_wid')) {
            return $this->redirect($this->get('router')->generate('cb_newage_panel_view_view', ['id' => $panelView->getId()]));
        }

        return array(
            'entity'  => $panelView,
        );
    }


    /**
     * @Route("/create", name="cb_newage_panel_view_create")
     * @AclAncestor("cb_newage_panel_view_create")
     * @Template("CBNewAgeBundle:PanelView:update.html.twig")
     */
    public function createAction()
    {
        $panelView = $this->get('cb_newage_panel_view.manager')->createPanelView();

        return $this->update($panelView);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_panel_view_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_panel_view_update")
     */
    public function updateAction(PanelView $panelView)
    {
        return $this->update($panelView);
    }

    /**
     * @param PanelView $panelView
     * @return array
     */
    protected function update(PanelView $panelView)
    {
        if ($this->get('cb_newage_panel_view.form.handler.entity')->process($panelView)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.panelview.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_panel_view_update', 'parameters' => ['id' => $panelView->getId()]],
                ['route' => 'cb_newage_panel_view_view', 'parameters' => ['id' => $panelView->getId()]]
            );
        }

        return array(
            'entity' => $panelView,
            'form' => $this->get('cb_newage_panel_view.form.entity')->createView()
        );
    }

    /**
     * @Route("/add/{id}", defaults={"id"=null}, name="cb_newage_panel_view_add")
     * @AclAncestor("cb_newage_panel_view_create")
     * @Template("CBNewAgeBundle:PanelView:update.html.twig")
     */
    public function addAction($id)
    {
        $panelView = $this->get('cb_newage_panel_view.manager')->createPanelView();

        if ($id != null) {
            $panel = $this->getDoctrine()
                ->getRepository('CBNewAgeBundle:Panel')->findOneBy(array('id' => $id));

            $panelView->setPanel( $panel );
        }

        if ($this->get('cb_newage_panel_view.form.handler.entity')->process($panelView)) {
            return array(
                'saved' => true,
                'entity' => $panelView,
                'form' => $this->get('cb_newage_panel_view.form.entity')->createView()
            );
        }

        return array(
            'entity' => $panelView,
            'form' => $this->get('cb_newage_panel_view.form.entity')->createView()
        );
    }
}