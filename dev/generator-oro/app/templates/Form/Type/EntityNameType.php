<?php
/**
 * Created by orm-generator.
 * User: <%= user %>
 * Date: <%= date %>
 * Time: <%= time %>
 */

namespace <%= BundleNamespace %>\Form\Type;

use <%= BundleNamespace %>\Entity\<%= EntityName %>;
use <%= BundleNamespace %>\Entity\<%= EntityName %>Type as <%= EntityName %>TypeEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;

class <%= EntityName %>Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => '<%= BundleBase %>\\Bundle\\<%= BundleShortName %>Bundle\\Entity\\<%= EntityName %>',
                'intention' => '<%= bundle_name %>_<%= entity_name %>_entity',
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
        return '<%= bundle_name %>_<%= entity_name %>';
    }

}