<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 21/Jun/16
 * Time: 15:10
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_client_index")
     * @Template()
     * @AclAncestor("cb_newage_client_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_client_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_client_view")
     */
    public function viewAction(Client $client)
    {
        return [
            'entity' => $client
        ];
    }

    /**
     * @Route("/create", name="cb_newage_client_create")
     * @AclAncestor("cb_newage_client_create")
     * @Template("CBNewAgeBundle:Client:update.html.twig")
     */
    public function createAction()
    {
        $client = $this->get('cb_newage_client.manager')->createClient();

        return $this->update($client);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_client_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_client_update")
     */
    public function updateAction(Client $client)
    {
        return $this->update($client);
    }

    /**
     * @param Client $client
     * @return array
     */
    protected function update(Client $client)
    {
        if ($this->get('cb_newage_client.form.handler.entity')->process($client)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.client.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_client_update', 'parameters' => ['id' => $client->getId()]],
                ['route' => 'cb_newage_client_view', 'parameters' => ['id' => $client->getId()]]
            );
        }

        return array(
            'entity' => $client,
            'form' => $this->get('cb_newage_client.form.entity')->createView()
        );
    }

    /**
     * @Route("/add", name="cb_newage_client_add")
     * @AclAncestor("cb_newage_client_create")
     * @Template("CBNewAgeBundle:Client:update.html.twig")
     */
    public function addAction()
    {
        $client = $this->get('cb_newage_client.manager')->createClient();

        if ($this->get('cb_newage_client.form.handler.entity')->process($client)) {
            return array(
                'saved' => true,
                'entity' => $client,
                'form' => $this->get('cb_newage_client.form.entity')->createView()
            );
        }

        return array(
            'entity' => $client,
            'form' => $this->get('cb_newage_client.form.entity')->createView()
        );
    }
}