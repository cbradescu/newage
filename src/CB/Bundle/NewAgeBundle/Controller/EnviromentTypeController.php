<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 09:44
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\EnviromentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/enviroment_type")
 */
class EnviromentTypeController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_enviroment_type_index")
     * @Template()
     * @AclAncestor("cb_newage_enviroment_type_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_enviroment_type_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_enviroment_type_view")
     */
    public function viewAction(EnviromentType $enviromentType)
    {
        return [
            'entity' => $enviromentType
        ];
    }

    /**
     * @Route("/create", name="cb_newage_enviroment_type_create")
     * @AclAncestor("cb_newage_enviroment_type_create")
     * @Template("CBNewAgeBundle:EnviromentType:update.html.twig")
     */
    public function createAction()
    {
        $enviromentType = $this->get('cb_newage_enviroment_type.manager')->createEnviromentType();

        return $this->update($enviromentType);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_enviroment_type_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_enviroment_type_update")
     */
    public function updateAction(EnviromentType $enviromentType)
    {
        return $this->update($enviromentType);
    }

    /**
     * @param EnviromentType $enviromentType
     * @return array
     */
    protected function update(EnviromentType $enviromentType)
    {
        if ($this->get('cb_newage_enviroment_type.form.handler.entity')->process($enviromentType)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb_newage.enviromenttype.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_enviroment_type_update', 'parameters' => ['id' => $enviromentType->getId()]],
                ['route' => 'cb_newage_enviroment_type_view', 'parameters' => ['id' => $enviromentType->getId()]]
            );
        }

        return array(
            'entity' => $enviromentType,
            'form' => $this->get('cb_newage_enviroment_type.form.entity')->createView()
        );
    }
}