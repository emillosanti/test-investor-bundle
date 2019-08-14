<?php

namespace SAM\InvestorBundle\Queue;

use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Consumption\QueueSubscriberInterface;
use Enqueue\Consumption\Result;
use Enqueue\Util\JSON;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use SAM\InvestorBundle\Manager\InvestmentValuesManager;
use SAM\InvestorBundle\Traits\InvestorListenersAndEventsTrait;
use SAM\SearchBundle\Manager\SearchEngineManager;

final class UpdateInvestmentValuesProcessor implements Processor, CommandSubscriberInterface, QueueSubscriberInterface
{
    use InvestorListenersAndEventsTrait;

    /** @var InvestmentValuesManager */
    private $investmentValuesManager;

    /** @var SearchEngineManager */
    private $searchEngineManager;

    /**
     * @param InvestmentValuesManager $investmentValuesManager
     * @param SearchEngineManager $searchEngineManager
     */
    public function __construct(
        InvestmentValuesManager $investmentValuesManager,
        SearchEngineManager $searchEngineManager
    )
    {
        $this->investmentValuesManager = $investmentValuesManager;
        $this->searchEngineManager = $searchEngineManager;
    }

    public function process(Message $psrMessage, Context $psrContext)
    {
        $message = UpdateInvestmentValues::jsonDeserialize($psrMessage->getBody());

        try {
            $this->investmentValuesManager->doUpdateInvestmentValues(null, true);

            return Result::reply($psrContext->createMessage(JSON::encode([
                'status' => true,
            ])));
        } catch (\Exception $e) {
            return Result::reply($psrContext->createMessage(JSON::encode([
                'status' => false,
                'exception' => $e->getMessage(),
            ])), Result::REQUEUE, $e->getMessage());
        }
    }

    public static function getSubscribedCommand(): array
    {
        return [
            'command' => Commands::UPDATE_INVESTMENT_VALUES,
            'queue' => Commands::UPDATE_INVESTMENT_VALUES,
            'prefix_queue' => true,
            'exclusive' => true,
        ];
    }

    public static function getSubscribedQueues(): array
    {
        return [Commands::UPDATE_INVESTMENT_VALUES];
    }
}
