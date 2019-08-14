<?php

namespace SAM\InvestorBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use SAM\CommonBundle\Entity\Interaction;
use SAM\CommonBundle\Event\InteractionEvent;
use SAM\CommonBundle\Event\InteractionEvents;

class InteractionSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return [
            InteractionEvents::INTERACTION_CREATE_SUCCESS => 'onInteractionCreateSuccess',
            InteractionEvents::INTERACTION_EDIT_SUCCESS => 'onInteractionEditSuccess',
        ];
    }

    public function onInteractionCreateSuccess(InteractionEvent $event)
    {
        if ($event && $event->getEntity() instanceof InvestorLegalEntity) {
        	$event->getEntity()
                ->getInvestor()
                ->setInteractedAt(new \DateTime());
        }
    }

    public function onInteractionEditSuccess(InteractionEvent $event)
    {
        if ($event && $event->getEntity() instanceof InvestorLegalEntity) {
        	$event->getEntity()
                ->getInvestor()
                ->setInteractedAt($event->getInteraction()->getEventDate());
        }
    }
}