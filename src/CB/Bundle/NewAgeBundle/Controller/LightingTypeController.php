<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 11:41
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\LightingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/lighting_type")
 */
class LightingTypeController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_lighting_type_index")
     * @Template()
     * @AclAncestor("cb_newage_lighting_type_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_lighting_type_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_lighting_type_view")
     */
    public function viewAction(LightingType $lightingType)
    {
        return [
            'entity' => $lightingType
        ];
    }

    /**
     * @Route("/create", name="cb_newage_lighting_type_create")
     * @AclAncestor("cb_newage_lighting_type_create")
     * @Template("CBNewAgeBundle:LightingType:update.html.twig")
     */
    public function createAction()
    {
        $lightingType = $this->get('cb_newage_lighting_type.manager')->createLightingType();

        return $this->update($lightingType);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_lighting_type_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_lighting_type_update")
     */
    public function updateAction(LightingType $lightingType)
    {
        return $this->update($lightingType);
    }

    /**
     * @param LightingType $lightingType
     * @return array
     */
    protected function update(LightingType $lightingType)
    {
        if ($this->get('cb_newage_lighting_type.form.handler.entity')->process($lightingType)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb_newage.lightingtype.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_lighting_type_update', 'parameters' => ['id' => $lightingType->getId()]],
                ['route' => 'cb_newage_lighting_type_view', 'parameters' => ['id' => $lightingType->getId()]]
            );
        }

        return array(
            'entity' => $lightingType,
            'form' => $this->get('cb_newage_lighting_type.form.entity')->createView()
        );
    }
}