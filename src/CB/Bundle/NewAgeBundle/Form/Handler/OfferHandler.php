<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 08:19
 */

namespace CB\Bundle\NewAgeBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

class OfferHandler extends ApiFormHandler
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

    /**
     * Process form
     *
     * @param mixed $entity
     *
     * @return mixed|null The instance of saved entity on successful processing; otherwise, null
     */
    public function process($entity)
    {
        $entity = $this->prepareFormData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($this->request);
            if ($this->form->isValid()) {
                return $this->onSuccess($entity) ?: $entity;
            }
        }

        return null;
    }

//    /**
//     * "Success" form handler
//     *
//     * @param mixed $entity
//     *
//     * @return mixed|null The instance of saved entity. Can be null if it is equal of the $entity argument
//     */
//    protected function onSuccess($entity)
//    {
//        if ($entity->get)
//        foreach ($entity->getItems() as $item)
//        {
//            $this->entityManager->persist($item);
//        }
//        $this->entityManager->persist($entity);
//        $this->entityManager->flush();
//    }

}