<?php

namespace SAM\InvestorBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use SAM\InvestorBundle\Entity\Investor;
use Doctrine\ORM\Event\LifecycleEventArgs;
use SAM\InvestorBundle\Manager\InvestmentValuesManager;
use SAM\InvestorBundle\Traits\InvestorListenersAndEventsTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InvestorListener implements EventSubscriber
{
    use InvestorListenersAndEventsTrait;

    /** @var InvestmentValuesManager */
    protected $investmentValuesManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * InvestorListener constructor.
     * @param InvestmentValuesManager $investmentValuesManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(InvestmentValuesManager $investmentValuesManager, TokenStorageInterface $tokenStorage)
    {
        $this->investmentValuesManager = $investmentValuesManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @ORM\PrePersist()
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Investor) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() && null === $object->getCreator()) {
            $object->setCreator($token->getUser());
        }
    }

    /**
     * @ORM\PostPersist()
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Investor) {
            return;
        }

        $this->investmentValuesManager->updateInvestmentValues($object->getId());
    }

    /**
     * @ORM\PostUpdate()
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Investor) {
            return;
        }

        $this->investmentValuesManager->updateInvestmentValues($object->getId());
    }

    /**
     * @ORM\PostRemove()
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof Investor) {
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
            Events::prePersist,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove
        ];
    }
}
