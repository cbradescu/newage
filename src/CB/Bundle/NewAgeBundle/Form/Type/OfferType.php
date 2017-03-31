<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Form\Type;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferType as OfferTypeEntity;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
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

class OfferType extends AbstractType
{
    /** @var Router  */
    var $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'required' => true,
                    'label'    => 'cb.newage.offer.name.label'
                ]
            )
            ->add(
                'start',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.newage.offer.start.label',
                    'attr'     => ['class' => 'start'],
                ]
            )
            ->add(
                'end',
                'oro_date',
                [
                    'required' => true,
                    'label'    => 'cb.newage.offer.end.label',
                    'attr'     => ['class' => 'end'],
                ]
            )
            ->add(
                'client',
                'entity',
                [
                    'label'       => 'cb.newage.client.entity_label',
                    'class'       => 'CBNewAgeBundle:Client',
                    'property'    => 'title',
                    'required' => true
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\Offer',
                'intention' => 'cb_newage_offer_entity',
                'cascade_validation' => true,
                'ownership_disabled' => true,
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
        return 'cb_newage_offer';
    }
}