<?php

namespace CB\Bundle\NewAgeBundle\Extension\MassAction;

use CB\Bundle\NewAgeBundle\Entity\Offer;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;


use Oro\Bundle\DataGridBundle\Exception\LogicException;
use Oro\Bundle\DataGridBundle\Datasource\Orm\DeletionIterableResult;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

use Oro\Bundle\SearchBundle\Async\Topics;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class RemoveMassActionHandler implements MassActionHandlerInterface
{
    const FLUSH_BATCH_SIZE = 100;

    /** @var RegistryInterface */
    protected $registry;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var OptionalListenerManager */
    protected $listenerManager;

    /** @var  MessageProducerInterface */
    protected $producer;

    /**
     * @var string
     */
    protected $responseMessage = 'cb.newage.reservation.datagrid.success_message';

    /**
     * @param RegistryInterface        $registry
     * @param TranslatorInterface      $translator
     * @param OptionalListenerManager  $listenerManager
     * @param SecurityFacade           $securityFacade
     * @param MessageProducerInterface $producer
     */
    public function __construct(
        RegistryInterface $registry,
        TranslatorInterface $translator,
        SecurityFacade $securityFacade,
        OptionalListenerManager $listenerManager,
        MessageProducerInterface $producer
    ) {
        $this->registry = $registry;
        $this->translator = $translator;
        $this->securityFacade= $securityFacade;
        $this->listenerManager = $listenerManager;
        $this->producer = $producer;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $offer = null;
        $iteration    = 0;
        $entityName   = $this->getEntityName($args);

        $queryBuilder = $args->getResults()->getSource();
        $results      = new DeletionIterableResult($queryBuilder);
        $results->setBufferSize(self::FLUSH_BATCH_SIZE);
        $this->listenerManager->disableListeners(['oro_search.index_listener']);
        // if huge amount data must be deleted
        set_time_limit(0);
        $deletedIds            = [];
        $entityIdentifiedField = $this->getEntityIdentifierField($args);
        /** @var EntityManager $manager */
        $manager = $this->registry->getManagerForClass($entityName);
        foreach ($results as $result) {
            /** @var $result ResultRecordInterface */
            $entity          = $result->getRootEntity();
            $identifierValue = $result->getValue($entityIdentifiedField);
            if (!$entity) {
                // no entity in result record, it should be extracted from DB
                $entity = $manager->getReference($entityName, $identifierValue);
            }

            if ($entity) {
                if (!$this->securityFacade->isGranted('DELETE', $entity)) {
                    continue;
                }
                $deletedIds[] = $identifierValue;
                $this->processDelete($entity, $manager);
                $iteration++;

                if ($iteration % self::FLUSH_BATCH_SIZE == 0) {
                    $this->finishBatch($manager, $entityName, $deletedIds);
                    $deletedIds = [];
                }

                switch ($entityName) {
                    case 'CB\Bundle\NewAgeBundle\Entity\OfferItem':
                        $offer = $entity->getOffer();
                        break;
                    case 'CB\Bundle\NewAgeBundle\Entity\ReservationItem':
                        $offer = $entity->getOfferItem()->getOffer();
                        break;
                }
            }
        }

        if ($iteration % self::FLUSH_BATCH_SIZE > 0) {
            $this->finishBatch($manager, $entityName, $deletedIds);
        }

        return $this->getDeleteResponse($args, $iteration, $offer);
    }

    /**
     * @param MassActionHandlerArgs $args
     *
     * @return string
     * @throws LogicException
     */
    protected function getEntityName(MassActionHandlerArgs $args)
    {
        $massAction = $args->getMassAction();
        $entityName = $massAction->getOptions()->offsetGet('entity_name');
        if (!$entityName) {
            throw new LogicException(sprintf('Mass action "%s" must define entity name', $massAction->getName()));
        }

        return $entityName;
    }

    /**
     * @param MassActionHandlerArgs $args
     *
     * @throws LogicException
     * @return string
     */
    protected function getEntityIdentifierField(MassActionHandlerArgs $args)
    {
        $massAction = $args->getMassAction();
        $identifier = $massAction->getOptions()->offsetGet('data_identifier');
        if (!$identifier) {
            throw new LogicException(sprintf('Mass action "%s" must define identifier name', $massAction->getName()));
        }

        // if we ask identifier that's means that we have plain data in array
        // so we will just use column name without entity alias
        if (strpos('.', $identifier) !== -1) {
            $parts      = explode('.', $identifier);
            $identifier = end($parts);
        }

        return $identifier;
    }

    /**
     * @param object $entity
     * @param EntityManager $manager
     *
     * @return RemoveMassActionHandler
     */
    protected function processDelete($entity, EntityManager $manager)
    {
        $manager->remove($entity);

        return $this;
    }

    /**
     * Finish processed batch
     *
     * @param EntityManager $manager
     * @param string        $entityName
     * @param array         $deletedIds
     */
    protected function finishBatch(EntityManager $manager, $entityName, array $deletedIds)
    {
        $body = [];
        foreach ($deletedIds as $deletedId) {
            $body [] = [
                'class' => $entityName,
                'id' => $deletedId
            ];
        }

        $manager->flush();

        $this->producer->send(Topics::INDEX_ENTITIES, $body);

        $manager->clear();
    }

    /**
     * @param MassActionHandlerArgs $args
     * @param int                   $entitiesCount
     *
     * @return MassActionResponse
     */
    protected function getDeleteResponse(MassActionHandlerArgs $args, $entitiesCount = 0, Offer $offer)
    {
        $massAction      = $args->getMassAction();
        $responseMessage = $massAction->getOptions()->offsetGetByPath('[messages][success]', $this->responseMessage);

        $successful = $entitiesCount > 0;
        $options    = [
            'count' => $entitiesCount,
            'offer' => $offer
        ];

        return new MassActionResponse(
            $successful,
            null,
            $options
        );
    }
}