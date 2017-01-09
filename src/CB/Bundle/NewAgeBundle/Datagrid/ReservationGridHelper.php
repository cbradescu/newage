<?php
namespace CB\Bundle\NewAgeBundle\Datagrid;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

/**
 * Used in grid of roles to provide permissions for actions on level of each role in the grid.
 */
class ReservationGridHelper
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns callback for configuration of grid/actions visibility per row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            return [
                'delete' => $record->getValue('available') == 2 ? false : true,
            ];
        };
    }
}
