<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 08:19
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\City;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/city")
 */
class CityController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_city_index")
     * @Template()
     * @AclAncestor("cb_newage_city_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_city_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_city_view")
     */
    public function viewAction(City $city)
    {
        return [
            'entity' => $city
        ];
    }

    /**
     * @Route("/create", name="cb_newage_city_create")
     * @AclAncestor("cb_newage_city_create")
     * @Template("CBNewAgeBundle:City:update.html.twig")
     */
    public function createAction()
    {
        $city = $this->get('cb_newage_city.manager')->createCity();

        return $this->update($city);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_city_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_city_update")
     */
    public function updateAction(City $city)
    {
        return $this->update($city);
    }

    /**
     * @param City $city
     * @return array
     */
    protected function update(City $city)
    {
        if ($this->get('cb_newage_city.form.handler.entity')->process($city)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.city.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_city_update', 'parameters' => ['id' => $city->getId()]],
                ['route' => 'cb_newage_city_view', 'parameters' => ['id' => $city->getId()]]
            );
        }

        return array(
            'entity' => $city,
            'form' => $this->get('cb_newage_city.form.entity')->createView()
        );
    }
}