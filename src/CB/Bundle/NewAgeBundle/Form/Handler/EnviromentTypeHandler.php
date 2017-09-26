<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 09:44
 */

namespace CB\Bundle\NewAgeBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

class EnviromentTypeHandler extends ApiFormHandler
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $manager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        EventDispatcherInterface $dispatcher
    )
    {
        parent::__construct($form, $request, $manager);
        $this->dispatcher = $dispatcher;
    }

}