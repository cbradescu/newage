<?php

namespace CB\Bundle\NewAgeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Oro\Bundle\AvailableBundle\Entity\Calendar;
use Oro\Bundle\AvailableBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\AvailableBundle\Provider\CalendarDateTimeConfigProvider;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class AvailableController extends Controller
{
    /**
     * @Route("/available/{month}", name="cb_newage_available_monthly")
     * @Template()
     * @AclAncestor("cb_newage_available_view")
     */
    public function monthlyAction($month = null)
    {
        if (!$month)
            $month = date('m');

        return [
            'month' => $month
        ];
    }

    /**
     * @Route("/day/{id}/{day}", name="cb_newage_available_day",
     *      requirements={
     *          "id"="\d+",
     *          "day"="\d+"
     *      },
     * )
     * @Template()
     * @return array
     */
    public function dayAction($id = null, $day = null)
    {
        $currentDay = date('Y-m-d', $day);

        $campaigns = null;

        if ($id && $currentDay) {
            $manager = $this->getDoctrine()->getRepository('CBNewAgeBundle:Campaign');
            $qb = $manager->createQueryBuilder('c');
            $qb->leftJoin('c.panelViews', 'pv');
            $qb->andWhere($qb->expr()->between(':currentDay', 'c.start', 'c.end'));
            $qb->andWhere('pv.id=:id');
            $qb->setParameter('currentDay', $currentDay);
            $qb->setParameter('id', $id);

            $campaigns = $qb->getQuery()->getResult();
        }

        return [
            'panel_view_id' => $id,
            'day' => $currentDay,
            'campaigns' => $campaigns,
        ];
    }
}
