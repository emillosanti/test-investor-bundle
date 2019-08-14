<?php

namespace SAM\InvestorBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use Doctrine\ORM\Event\LifecycleEventArgs;
use SAM\InvestorBundle\Manager\InvestmentValuesManager;
use SAM\InvestorBundle\Traits\InvestorListenersAndEventsTrait;

class InvestorLegalEntityListener implements EventSubscriber
{
    use InvestorListenersAndEventsTrait;

    /** @var InvestmentValuesManager */
    protected $investmentValuesManager;

    /**
     * InvestorLegalEntityListener constructor.
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
        if (!$object instanceof InvestorLegalEntity) {
            return;
        }

        $this->investmentValuesManager->updateInvestmentValues($object->getInvestor()->getId());
    }

    /**
     * @ORM\PostUpdate()
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof InvestorLegalEntity) {
            return;
        }

        $this->investmentValuesManager->updateInvestmentValues($object->getInvestor()->getId());
    }

    /**
     * @ORM\PostRemove()
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof InvestorLegalEntity) {
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
            Events::postRemove,
        ];
    }
}
