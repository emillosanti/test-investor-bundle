<?php

namespace SAM\InvestorBundle\Twig;

use SAM\InvestorBundle\Entity\Investor;
use SAM\InvestorBundle\Entity\InvestorStep;
use SAM\InvestorBundle\Repository\InvestorRepositoryInterface;
use SAM\SearchBundle\Manager\SearchEngineManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


/**
 * Class InvestorExtension
 */
class InvestorExtension extends AbstractExtension
{
    /**
     * @var InvestorRepositoryInterface
     */
    private $investorRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /** @var SearchEngineManager */
    private $searchEngineManager;

    /**
     * InvestorExtension constructor
     *.
     * @param TokenStorageInterface $tokenStorage
     * @param SearchEngineManager $searchEngineManager
     */
    public function __construct(TokenStorageInterface $tokenStorage ,SearchEngineManager $searchEngineManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->searchEngineManager = $searchEngineManager;
        $this->investorRepository = $this->searchEngineManager->getDoctrineRepository(InvestorRepositoryInterface::class);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('get_investor_interactions', [$this, 'getInvestorInteractions'])
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('count_investor_by_step', [$this, 'countInvestorByStep']),
            new TwigFunction('count_total_active_investors', [$this, 'countTotalActiveInvestors']),
            new TwigFunction('count_highlight_investor_by_step', [$this, 'countHighlightInvestorByStep']),
            new TwigFunction('count_highlight_investor', [$this, 'countHighlightInvestor']),

            // Algolia Facets
            new TwigFunction('count_investor_by_step_facets', [$this, 'countInvestorByStepFacets']),
            new TwigFunction('count_total_active_investors_facets', [$this, 'countTotalActiveInvestorsFacets']),
        ];
    }

    /**
     * @param Investor $investor
     *
     * @return array
     */
    public function getInvestorInteractions($investor)
    {
        $buffer = array_merge(
            $investor->getInteractionNotes()->toArray(),
            $investor->getInteractionEmails()->toArray(),
            $investor->getInteractionCalls()->toArray(),
            $investor->getInteractionLetters()->toArray(),
            $investor->getInteractionAppointments()->toArray()
        );
        usort($buffer, function ($investorInteractionA, $investorInteractionB) {
            if ($investorInteractionA->getEventDate()->getTimestamp() === $investorInteractionB->getEventDate()->getTimestamp()) {
                return 0;
            }

            return ($investorInteractionA->getEventDate()->getTimestamp() > $investorInteractionB->getEventDate()->getTimestamp()) ? -1 : 1;
        });

        return $buffer;
    }

    /**
     * @param $investorStep
     * @return int
     */
    public function countInvestorByStep($investorStep)
    {
        return $this->investorRepository->countActiveByStep($investorStep);
    }

    /**
     * @return int
     */
    public function countTotalActiveInvestors()
    {
        return $this->investorRepository->countTotalActiveInvestors();
    }

    /**
     * @param $investorStep
     * @return int
     */
    public function countHighlightInvestorByStep($investorStep)
    {
        return $this->investorRepository->countHighlightInvestorByStep($investorStep);
    }

    /**
     * @return int
     */
    public function countHighlightInvestor()
    {
        return $this->investorRepository->countHighlightInvestor();
    }

    /**
     * @param InvestorStep $investorStep
     * @param array|null $facets
     * @return int
     */
    public function countInvestorByStepFacets(InvestorStep $investorStep, $facets = null)
    {
        return $facets['currentStep.id'][$investorStep->getId()] ?? 0;
    }

    /**
     * @param null $facets
     * @return int
     */
    public function countTotalActiveInvestorsFacets($facets = null)
    {
        return $facets['currentStep.id'] ? array_sum($facets['currentStep.id']) : 0;
    }


}
