<?php
/**
 * Created by orm-generator.
 * User: <%= user %>
 * Date: <%= date %>
 * Time: <%= time %>
 */
namespace <%= BundleNamespace %>\Controller;

use <%= BundleNamespace %>\Entity\<%= EntityName %>;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/<%= entity_name %>")
 */
class <%= EntityName %>Controller extends Controller
{
    /**
     * @Route("/index", name="<%= bundle_name%>_<%= entity_name %>_index")
     * @Template()
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="<%= bundle_name %>_<%= entity_name %>_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_view")
     */
    public function viewAction(<%= EntityName %> $<%= entityName %>)
    {
        return [
            'entity' => $<%= entityName %>
        ];
    }

    /**
     * @Route("/create", name="<%= bundle_name %>_<%= entity_name %>_create")
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_create")
     * @Template("<%= BundleName %>:<%= EntityName %>:update.html.twig")
     */
    public function createAction()
    {
        $<%= entityName %> = $this->get('<%= bundle_name %>_<%= entity_name %>.manager')->create<%= EntityName %>();

        return $this->update($<%= entityName %>);
    }

    /**
     * @Route("/update/{id}", name="<%= bundle_name %>_<%= entity_name %>_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_update")
     */
    public function updateAction(<%= EntityName %> $<%= entityName %>)
    {
        return $this->update($<%= entityName %>);
    }

    /**
     * @param <%= EntityName %> $<%= entityName %>
     * @return array
     */
    protected function update(<%= EntityName %> $<%= entityName %>)
    {
        if ($this->get('<%= bundle_name %>_<%= entity_name %>.form.handler.entity')->process($<%= entityName %>)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('<%= bundle_name %>.<%= entity_name %>.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => '<%= bundle_name %>_<%= entity_name %>_update', 'parameters' => ['id' => $<%= entityName %>->getId()]],
                ['route' => '<%= bundle_name %>_<%= entity_name %>_view', 'parameters' => ['id' => $<%= entityName %>->getId()]]
            );
        }

        return array(
            'entity' => $<%= entityName %>,
            'form' => $this->get('<%= bundle_name %>_<%= entity_name %>.form.entity')->createView()
        );
    }
}