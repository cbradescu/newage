<?php

namespace CB\Bundle\NewAgeBundle\Workflow\Action;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferItem;

use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Exception\NotManageableEntityException;
use Oro\Component\Action\Model\ContextAccessor;
/**
 * - @generate_offer_items:
 *     offer: $currentOffer
 */
class GenerateOfferItemsAction extends AbstractAction
{
    /** @var array */
    protected $options;

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ContextAccessor $contextAccessor
     * @param ManagerRegistry $registry
     */
    public function __construct(ContextAccessor $contextAccessor, ManagerRegistry $registry)
    {
        parent::__construct($contextAccessor);

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        error_log('initialize', 3, '/var/www/newage/crm-application/app/logs/catalin');

        if (empty($options['offer'])) {
            throw new InvalidParameterException('Offer parameter must be specified');
        }
        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute parameters is required');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $entityClassName = 'CB\Bundle\NewAgeBundle\Entity\Offer';

        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManagerForClass($entityClassName);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClassName);
        }

        /** @var Offer $offer */
        $offer = $this->contextAccessor->getValue($context, $this->options['offer']);

        $items = $offer->getItems();
        foreach ($items as $item)
        {
            $offer->removeItem($item);
        }

        $entityManager->persist($offer);
        $entityManager->flush();

        $item = new OfferItem();
        $item->setOffer($offer);
        $item->setStart($offer->getStart());
        $item->setEnd($offer->getEnd());

        $offer->addItem($item);

        $entityManager->persist($offer);
        $entityManager->flush();

//        $this->contextAccessor->setValue($context, $this->options['attribute'], $items);
    }
}
