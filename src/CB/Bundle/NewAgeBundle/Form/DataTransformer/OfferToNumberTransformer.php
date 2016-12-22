<?php
namespace CB\Bundle\NewAgeBundle\Form\DataTransformer;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class OfferToNumberTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (offer) to a string (number).
     *
     * @param  Offer|null $offer
     * @return string
     */
    public function transform($offer)
    {
        if (null === $offer) {
            return '';
        }

        return $offer->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $offerNumber
     * @return Offer|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($offerNumber)
    {

        // no issue number? It's optional, so that's ok
        if (!$offerNumber) {
            return null;
        }

        /** @var Offer $offer */
        $offer = $this->manager
            ->getRepository('CBNewAgeBundle:Offer')
            // query for the issue with this id
            ->find($offerNumber);

        if (null === $offer) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An offer with number "%s" does not exist!',
                $offerNumber
            ));
        }

        return $offer;
    }
}