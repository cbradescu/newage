<?php

namespace CB\Bundle\NewAgeBundle\Extension;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\HttpFoundation\Request;

class AvailableDatasource implements DatasourceInterface
{
    const TYPE = 'available';

    /** @var ObjectManager */
    protected $em;

    /** @var DatagridInterface */
    protected $datagrid;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->em = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->datagrid = $grid;

        $grid->setDatasource(clone $this);
    }

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults()
    {
        $datagridParameters = $this->datagrid->getParameters()->all();
        $month = $datagridParameters['month'];

        $qb = $this->em->getRepository('CBNewAgeBundle:PanelView')
            ->createQueryBuilder('pv')
            ->leftJoin('pv.panel', 'p');

        $results = $qb->getQuery()->getResult();

        $rows    = [];
        foreach ($results as $result) {
            $currentRow = [];
            $currentRow['panelViewId'] = $result->getId();
            $currentRow['panelViewName'] = $result->getName();
            $currentRow['panelName'] = $result->getPanel()->getName();

            for ($day=1; $day<=31;$day++) {
                $status = 0; // liber

                if (checkdate(date('m'), $day, date('Y'))) {
                    $currentDay = date('Y-m-d', mktime(0,0,0, $month, $day, date('Y')));

                    $qb = $this->em->getRepository('CBNewAgeBundle:Campaign')->createQueryBuilder('c');
                    $qb->leftJoin('c.panelViews', 'pv');
                    $qb->andWhere($qb->expr()->between(':currentDay', 'c.start', 'c.end'));
                    $qb->andWhere('pv.id=:panelViewId');
                    $qb->setParameter('currentDay', $currentDay);
                    $qb->setParameter('panelViewId', $result->getId());

                    $campaigns = $qb->getQuery()->getResult();

                    foreach ($campaigns as $campaign) {
                        if ($status==0) {
                            if ($campaign->isConfirmed())
                                $status = 2; // confirmat
                            else
                                $status = 1; // rezervat
                        } else
                            $status = 3; // suprapunere

                    }
                    $currentRow['d' . $day] = [
                        'day' => strtotime($currentDay),
                        'status' => $status
                    ];
                } else {
                    $currentRow['d' . $day] = [
                        'day' => null,
                        'status' => 0
                    ];
                }
            }
            $rows[] = new ResultRecord($currentRow);
        }

        return $rows;
    }
}
