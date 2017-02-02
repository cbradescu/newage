<?php

namespace CB\Bundle\NewAgeBundle\Mailer;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

/**
 * Class Processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Processor
{
    /** @var DelegatingEngine */
    protected $templating;

    /** @var ConfigManager */
    protected $configManager;

    /** @var \Swift_Mailer */
    protected $mailer;

    /**
     * @param DelegatingEngine $templating
     * @param ConfigManager $configManager
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        DelegatingEngine $templating,
        ConfigManager $configManager,
        \Swift_Mailer $mailer = null
    ) {
        $this->templating = $templating;
        $this->configManager = $configManager;
        $this->mailer        = $mailer;
    }

    /**
     * @param Offer $confirmedOffer
     * @param Offer $affectedOffer
     */
    public function sendReservationChangeEmail(Offer $confirmedOffer, Offer $affectedOffer)
    {
        $senderEmail = $this->configManager->get('oro_notification.email_notification_sender_email');
        $senderName  = $this->configManager->get('oro_notification.email_notification_sender_name');

        $message = \Swift_Message::newInstance()
            ->setSubject('CRM - Modificare oferta/rezervare ')
            ->setFrom($senderEmail, $senderName)
            ->setTo($affectedOffer->getOwner()->getEmail())
            ->setBody(
                $this->templating->render(
                    'CBNewAgeBundle:Mail:reservationChange.html.twig',
                    ['confirmedOffer' => $confirmedOffer, 'affectedOffer' => $affectedOffer]
                ),
                'text/html'
            );

        $this->mailer->send($message);

        error_log($message);
    }
}