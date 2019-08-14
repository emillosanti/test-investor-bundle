<?php

namespace SAM\InvestorBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Entity\InvestorLegalEntityDetails;
use Doctrine\ORM\Event\LifecycleEventArgs;
use SAM\InvestorBundle\Manager\InvestmentValuesManager;
use SAM\InvestorBundle\Traits\InvestorListenersAndEventsTrait;

class InvestorLegalEntityDetailsListener implements EventSubscriber
{
    use InvestorListenersAndEventsTrait;

    /** @var InvestmentValuesManager */
    protected $investmentValuesManager;

    /**
     * InvestorLegalEntityDetailsListener constructor.
     * @param InvestmentValuesManager $investmentValuesManager
     */
    public function __construct(InvestmentValuesManager $investmentValuesManager)
    {
        $this->investmentValuesManager = $investmentValuesManager;
    }

    /**
     * @ORM\PostPersist()
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof InvestorLegalEntityDetails) {
            return;
        }

        if ($object->getInvestorLegalEntity()) {
            $this->investmentValuesManager->updateInvestmentValues($object->getInvestorLegalEntity()->getInvestor()->getId());
        }
    }

    /**
     * @ORM\PostUpdate()
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof InvestorLegalEntityDetails) {
            return;
        }

        if ($object->getInvestorLegalEntity()) {
            $this->investmentValuesManager->updateInvestmentValues($object->getInvestorLegalEntity()->getInvestor()->getId());
        }
    }

    /**
     * @ORM\PostRemove()
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof InvestorLegalEntityDetails) {
            return;
        }

        $this->investmentValuesManager->updateInvestmentValues();
    }
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove
        ];
    }
}
