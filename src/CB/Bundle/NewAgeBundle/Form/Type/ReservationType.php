<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 23/Nov/16
 * Time: 12:43
 */

namespace CB\Bundle\NewAgeBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use CB\Bundle\NewAgeBundle\Entity\Reservation;
use CB\Bundle\NewAgeBundle\Entity\ReservationType as ReservationTypeEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;
use CB\Bundle\NewAgeBundle\Form\DataTransformer\OfferToNumberTransformer;

class ReservationType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager  $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager  = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        @todo: remove transformer
//        $transformer = new OfferToNumberTransformer($this->entityManager);

//        $builder
//            ->add(
//                $builder->create(
//                    'offer',
//                    'hidden'
////                    [
////                        'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\Offer'
////                    ]
//                )
//                ->addModelTransformer($transformer)
//                ->addViewTransformer($transformer)
//            );

        $builder
            ->add(
                'offer',
                'entity',
                array(
                    'label' => 'cb.newage.offer.entity_label',
                    'class' => 'CBNewAgeBundle:Offer',
                    'property' => 'name',
                    'required' => true
                )
            );

        $this->addPanelViews($builder);
    }

    protected function addPanelViews(FormBuilderInterface $builder)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Reservation $data */
                $data = $event->getData();
                $form = $event->getForm();

                $form->add(
                    'reservedPanelViews',
                    'cb_panel_view_multiple_entity',
                    array(
                        'add_acl_resource' => 'cb_newage_panel_view_view',
                        'class' => 'CBNewAgeBundle:PanelView',
                        'selector_window_title' => 'cb.newage.offer.form.select_panel_views',
                        'selection_route' => 'cb_offer_widget_panel_views_info',
                        'selection_route_parameters' => ['id' => ($data != null && $data->getOffer() != null) ? $data->getOffer()->getId() : null],
                        'required' => true
                    )
                );
            });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'CB\\Bundle\\NewAgeBundle\\Entity\\Reservation',
                'intention' => 'cb_newage_reservation_entity',
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
        return 'cb_newage_reservation';
    }

}