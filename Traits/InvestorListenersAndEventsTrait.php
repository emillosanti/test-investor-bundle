<?php

namespace SAM\InvestorBundle\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use SAM\InvestorBundle\EventListener\InvestorLegalEntityDetailsListener;
use SAM\InvestorBundle\EventListener\InvestorLegalEntityListener;
use SAM\InvestorBundle\EventListener\InvestorListener;

trait InvestorListenersAndEventsTrait
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $listeners = [];

    /**
     * @required
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @required
     * @param InvestorListener $investorListener
     */
    public function setInvestorListener(InvestorListener $investorListener)
    {
        $this->listeners[] = $investorListener;
    }

    /**
     * @required
     * @param InvestorLegalEntityListener $investorLegalEntityListener
     */
    public function setInvestorLegalEntityListener(InvestorLegalEntityListener $investorLegalEntityListener)
    {
        $this->listeners[] = $investorLegalEntityListener;
    }

    /**
     * @required
     * @param InvestorLegalEntityDetailsListener $investorLegalEntityDetailsListener
     */
    public function setInvestorLegalEntityDetailsListener(InvestorLegalEntityDetailsListener $investorLegalEntityDetailsListener)
    {
        $this->listeners[] = $investorLegalEntityDetailsListener;
    }

    /**
     * @param Events[]|array $events
     */
    private function disableEvents($events)
    {
        $eventManager = $this->entityManager->getEventManager();
        foreach ($this->listeners as $listener) {
            if ($eventManager) {
                $eventManager->removeEventListener(
                    $events,
                    $listener
                );
            }
        }
    }

    /**
     * @param Events[]|array $events
     */
    private function enableEvents($events)
    {
        $eventManager = $this->entityManager->getEventManager();
        foreach ($this->listeners as $listener) {
            if ($eventManager) {
                $eventManager->addEventListener(
                    $events,
                    $listener
                );
            }
        }
    }
}