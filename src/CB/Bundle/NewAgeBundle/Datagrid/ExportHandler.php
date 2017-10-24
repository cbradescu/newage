<?php

namespace CB\Bundle\NewAgeBundle\Datagrid;

use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Oro\Bundle\ImportExportBundle\File\FileSystemOperator;
use Oro\Bundle\BatchBundle\Step\StepExecutionWarningHandlerInterface;

use Oro\Bundle\BatchBundle\Step\StepExecutor;
use Oro\Bundle\DataGridBundle\Exception\InvalidArgumentException;
use oro\Bundle\ImportExportBundle\Processor\ExportProcessor;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\Context;

class ExportHandler implements StepExecutionWarningHandlerInterface
{
    /**
     * @var FileSystemOperator
     */
    protected $fileSystemOperator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param FileSystemOperator $fileSystemOperator
     */
    public function __construct(FileSystemOperator $fileSystemOperator, EntityManager $entityManager)
    {
        $this->fileSystemOperator = $fileSystemOperator;
        $this->entityManager = $entityManager;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ItemReaderInterface $reader
     * @param ExportProcessor     $processor
     * @param ItemWriterInterface $writer
     * @param array               $contextParameters
     * @param int                 $batchSize
     * @param string              $format
     *
     * @return BinaryFileResponse
     */
    public function handle(
        ItemReaderInterface $reader,
        ExportProcessor $processor,
        ItemWriterInterface $writer,
        array $contextParameters,
        $batchSize,
        $format
    ) {
        if (!isset($contextParameters['gridName'])) {
            throw new InvalidArgumentException('Parameter "gridName" must be provided.');
        }

        /** @var Offer $offer */
        $offer = $this->entityManager
            ->getRepository('CBNewAgeBundle:Offer')
            ->find($contextParameters['gridParameters']['offer']);

        $filePath = $this
            ->fileSystemOperator
            ->generateTemporaryFileName(sprintf('Export_%s', $this->getFilename($contextParameters['gridName']) . '_' . $offer->getName()), $format);

        $contextParameters['filePath'] = $filePath;


        $context  = new Context($contextParameters);
        $executor = new StepExecutor();
        $executor->setBatchSize($batchSize);
        $executor
            ->setReader($reader)
            ->setProcessor($processor)
            ->setWriter($writer);
        foreach ([$executor->getReader(), $executor->getProcessor(), $executor->getWriter()] as $element) {
            if ($element instanceof ContextAwareInterface) {
                $element->setImportExportContext($context);
            }
        }

        $executor->execute($this);

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filePath)
        );

        return $response;
    }

    /**
     * @param object $element
     * @param string $name
     * @param string $reason
     * @param array $reasonParameters
     * @param mixed $item
     */
    public function handleWarning($element, $name, $reason, array $reasonParameters, $item)
    {
        $this->logger->error(sprintf('[DataGridExportHandle] Error message: %s', $reason), ['element' => $element]);
    }

    /**
     * @param string $gridName
     * @return string
     */
    protected function getFilename($gridName)
    {
        switch ($gridName)
        {
            case 'confirmed-items-grid':
                return 'Comanda_Ferma';
                break;
            case 'reservation-items-grid':
                return 'Rezervare';
                break;
            case 'offer-items-grid':
                return 'Ofertare';
                break;
            default:
                return 'Grid';
                break;
        }
    }
}