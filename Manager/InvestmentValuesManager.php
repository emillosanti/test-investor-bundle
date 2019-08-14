<?php

namespace SAM\InvestorBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Enqueue\Client\ProducerInterface;
use Psr\Log\LoggerInterface;
use SAM\CommonBundle\Entity\LegalEntity;
use SAM\CommonBundle\Utils\Utils;
use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use SAM\InvestorBundle\Queue\Commands;
use SAM\InvestorBundle\Queue\UpdateInvestmentValues;
use SAM\InvestorBundle\Traits\InvestorListenersAndEventsTrait;
use SAM\SearchBundle\Manager\SearchEngineManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class InvestmentValuesManager
 */
class InvestmentValuesManager
{
    use InvestorListenersAndEventsTrait;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var SearchEngineManager */
    protected $searchEngineManager;

    /** @var Investor[]|ArrayCollection */
    protected $investors;

    /** @var ProducerInterface */
    protected $producer;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * InvestmentValuesManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param ManagerRegistry $registry
     * @param SearchEngineManager $searchEngineManager
     * @param ProducerInterface $producer
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        ManagerRegistry $registry,
        SearchEngineManager $searchEngineManager,
        ProducerInterface $producer,
        LoggerInterface $logger
    )
    {
        $this->em = $em;
        $this->registry = $registry;
        $this->searchEngineManager = $searchEngineManager;
        $this->producer = $producer;
        $this->logger = $logger;

        $this->investors = new ArrayCollection();
    }

    /**
     * Update investment values
     *
     * @param int $investorId Investor ID
     */
    public function updateInvestmentValues($investorId = null)
    {
        try {
            if ($investorId) {
                $this->doUpdateInvestmentValues([$investorId]);
            }

            $this->producer->sendCommand(
                Commands::UPDATE_INVESTMENT_VALUES,
                new UpdateInvestmentValues()
            );
        } catch (\Exception $e) {
            $this->logger->critical('An error occurred during investor values update - could not send message to queue', [
                'context' => InvestmentValuesManager::class,
                'module' => 'investor.update_values',
                'exception' => $e
            ]);
            $this->doUpdateInvestmentValues();
        }
    }

    /**
     * @param array $investorIds Array of Investor IDs
     * @param bool $fromQueue
     */
    public function doUpdateInvestmentValues(array $investorIds = null, $fromQueue = false)
    {
        // NB! The order of the steps below is important!

        # 1. Disable listener events
        $this->disableEvents([Events::postPersist, Events::postUpdate]);

        # 2. Flush pending changes. Get a new entity manager
        $this->flush($fromQueue);

        # 3. Load (fresh) Investors
        $investors = $this->getInvestors($investorIds);

        # 4. Investor's total investment values
        foreach ($investors as $investor) {
            $this->updateInvestorTotalInvestmentAmount($investor);
            $this->updateInvestorTotalInvestmentPercentage($investor);
        }

        # 5. LegalEntity's funds raised values
        $this->updateAllLegalEntityFundsRaised();

        # 5. InvestorLegalEntity's investment values
        foreach ($investors as $investor) {
            $this->updateInvestorLegalEntityInvestmentPercentage($investor);
            $this->updateInvestorLegalEntityFundraiser($investor);
        }

        # 6. Flush pending changes
        $this->flush($fromQueue);

        $this->enableEvents([Events::postPersist, Events::postUpdate]);
    }

    /**
     * @param Investor $investor
     */
    private function updateInvestorTotalInvestmentAmount(Investor $investor)
    {
        $totalInvestmentAmount = 0;
        foreach ($investor->getInvestorLegalEntities() as $investorLegalEntity) {
            if ($investorLegalEntity->isInvestmentAmountOverridden()) {
                $totalInvestmentAmount += $investorLegalEntity->getInvestmentAmount();
            } else {
                $investorLegalEntityInvestmentAmount = 0;
                foreach ($investorLegalEntity->getDetails() as $details) {
                    $investorLegalEntityInvestmentAmount += $details->getAmount() * $details->getShareCategory()->getUnitPrice() / 1000; // € to k€
                    $totalInvestmentAmount += $investorLegalEntityInvestmentAmount;
                }

                $investorLegalEntity->setInvestmentAmount($investorLegalEntityInvestmentAmount);
                $this->em->persist($investorLegalEntity);
            }
        }

        $investor->setTotalInvestmentAmount($totalInvestmentAmount);
        $this->em->persist($investor);
    }

    /**
     * @param Investor $investor
     */
    private function updateInvestorTotalInvestmentPercentage(Investor $investor)
    {
        $totalInvestmentAmount = $investor->getTotalInvestmentAmount();
        $allInvestorsTotalInvestmentAmount = 0;

        foreach ($this->getInvestors() as $dbInvestor) {
            $allInvestorsTotalInvestmentAmount += $dbInvestor->getTotalInvestmentAmount();
        }

        $totalInvestmentPercentage = $allInvestorsTotalInvestmentAmount ?
            Utils::formatNumberWithoutRounding($totalInvestmentAmount / $allInvestorsTotalInvestmentAmount, 2) :
            0;

        $investor->setTotalInvestmentPercentage($totalInvestmentPercentage);
        $this->em->persist($investor);
    }

    /**
     * @param Investor $investor
     */
    private function updateInvestorLegalEntityInvestmentPercentage(Investor $investor)
    {
        foreach ($investor->getInvestorLegalEntities() as $investorLegalEntity) {
            $value = $investorLegalEntity->getLegalEntity()->getFundsRaised() ?
                $investorLegalEntity->getInvestmentAmount() / $investorLegalEntity->getLegalEntity()->getFundsRaised() :
                0;
            $investorLegalEntityInvestmentPercentage = Utils::formatNumberWithoutRounding($value, 2);

            $investorLegalEntity->setInvestmentPercentage($investorLegalEntityInvestmentPercentage);
            if (!$this->em->contains($investorLegalEntity)) {
                $this->em->persist($investorLegalEntity);
            }
        }
    }

    /**
     * @param Investor $investor
     */
    private function updateInvestorLegalEntityFundraiser(Investor $investor)
    {
        foreach ($investor->getInvestorLegalEntities() as $investorLegalEntity) {
            if ($investorLegalEntity->getFundraiser() && $investorLegalEntity->getFundraiser()->getFeesPercentage() > 0) {
                $investorLegalEntity->getFundraiser()->setFeesAmount(
                    $investorLegalEntity->getInvestmentAmount() * ($investorLegalEntity->getFundraiser()->getFeesPercentage() / 100)
                );
                $this->em->persist($investorLegalEntity);
            }
        }
    }

    private function updateAllLegalEntityFundsRaised()
    {
        /** @var LegalEntity[]|ArrayCollection $legalEntities */
        $legalEntities = $this->em->getRepository('legal_entity')
            ->findQueryBuilderInvestmentVehicule()->getQuery()->getResult();

        foreach ($legalEntities as $legalEntity) {
            $fundsRaised = 0;
            $investorLegalEntities = $legalEntity->getInvestorLegalEntities();
            /** @var InvestorLegalEntity $investorLegalEntity */
            foreach ($investorLegalEntities as $investorLegalEntity) {
                $fundsRaised += $investorLegalEntity->getInvestmentAmount();
            }

            $legalEntity->setFundsRaised($fundsRaised);
            $this->em->persist($legalEntity);
        }
    }

    /**
     * @param array|null $investorIds Array of Investor IDs
     * @return ArrayCollection|Investor[]|array
     */
    private function getInvestors(array $investorIds = null)
    {
        if ($investorIds) {
            $investors = [];
            foreach ($investorIds as $investorId) {
                $investors[] = $this->getInvestor($investorId);
            }

            return $investors;
        }

        return $this->em->getRepository('investor')->findAll();
    }

    /**
     * @param int $investorId
     * @return Investor
     */
    private function getInvestor($investorId)
    {
        return $this->em->getRepository('investor')->find($investorId);
    }

    /**
     * @param bool $fromQueue
     */
    private function flush($fromQueue = false)
    {
        $this->em->flush();

        if ($fromQueue) {
            $this->em->clear();
            $this->em = $this->registry->resetManager();
        }
    }
}
