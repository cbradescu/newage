<?php

namespace CB\Bundle\NewAgeBundle\Datagrid;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\EntityBundle\Grid\GridHelper as BaseGridHelper;

class MultipleChoiceGridHelper extends BaseGridHelper
{
    const CITY_CLASS_NAME = 'CB\Bundle\NewAgeBundle\Entity\City';
    const LIGHTING_TYPE_CLASS_NAME = 'CB\Bundle\NewAgeBundle\Entity\LightingType';
    const SUPPORT_TYPE_CLASS_NAME = 'CB\Bundle\NewAgeBundle\Entity\SupportType';

    /** @var Registry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Constructor
     *
     * @param Registry $registry
     * @param AclHelper $aclHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(Registry $registry, AclHelper $aclHelper, TranslatorInterface $translator)
    {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->translator = $translator;
    }

    /**
     * Returns callback for configuration of grid/actions visibility per row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if ($record->getValue('isSystem')) {
                return array('delete' => false);
            }
        };
    }

    /**
     * Returns email template type choice list
     *
     * @return array
     */
    public function getTypeChoices()
    {
        return [
            'html' => 'oro.email.datagrid.emailtemplate.filter.type.html',
            'txt'  => 'oro.email.datagrid.emailtemplate.filter.type.txt'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValues($class_name)
    {
        $result            = [];
//        $result['_empty_'] = $this->translator->trans('oro.email.datagrid.emailtemplate.filter.entityName.empty');

        $qb = $this->registry->getManager()->getRepository($class_name)->createQueryBuilder('c')
            ->select('c.id', 'c.name');

        foreach ($this->aclHelper->apply($qb)->getArrayResult() as $entity) {
            $result[$entity['id']] = $entity['name'];
        }

        return $result;
    }
}