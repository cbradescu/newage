<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 09:44
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\SupportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/support_type")
 */
class SupportTypeController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_support_type_index")
     * @Template()
     * @AclAncestor("cb_newage_support_type_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_support_type_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_support_type_view")
     */
    public function viewAction(SupportType $supportType)
    {
        return [
            'entity' => $supportType
        ];
    }

    /**
     * @Route("/create", name="cb_newage_support_type_create")
     * @AclAncestor("cb_newage_support_type_create")
     * @Template("CBNewAgeBundle:SupportType:update.html.twig")
     */
    public function createAction()
    {
        $supportType = $this->get('cb_newage_support_type.manager')->createSupportType();

        return $this->update($supportType);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_support_type_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_support_type_update")
     */
    public function updateAction(SupportType $supportType)
    {
        return $this->update($supportType);
    }

    /**
     * @param SupportType $supportType
     * @return array
     */
    protected function update(SupportType $supportType)
    {
        if ($this->get('cb_newage_support_type.form.handler.entity')->process($supportType)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb_newage.supporttype.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_support_type_update', 'parameters' => ['id' => $supportType->getId()]],
                ['route' => 'cb_newage_support_type_view', 'parameters' => ['id' => $supportType->getId()]]
            );
        }

        return array(
            'entity' => $supportType,
            'form' => $this->get('cb_newage_support_type.form.entity')->createView()
        );
    }
}