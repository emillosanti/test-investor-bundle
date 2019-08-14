<?php

namespace SAM\InvestorBundle\Command;

use Enqueue\Client\ProducerInterface;
use SAM\CommonBundle\Command\CommandTrait;
use SAM\InvestorBundle\Manager\InvestmentValuesManager;
use SAM\InvestorBundle\Queue\Commands;
use SAM\InvestorBundle\Queue\UpdateInvestmentValues;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateInvestmentValuesCommand.
 */
class UpdateInvestmentValuesCommand extends Command
{
    use CommandTrait;

    /** @var InvestmentValuesManager */
    protected $investmentValuesManager;

    /** @var ProducerInterface */
    protected $producer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * UpdateInvestmentValuesCommand class constructor
     *
     * @param InvestmentValuesManager $investmentValuesManager
     * @param ProducerInterface $producer
     * @param LoggerInterface $logger
     */
    public function __construct(
        InvestmentValuesManager $investmentValuesManager,
        ProducerInterface $producer,
        LoggerInterface $logger
    )
    {
        parent::__construct();

        $this->investmentValuesManager = $investmentValuesManager;
        $this->producer = $producer;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('app:investors:update-values')
            ->setDescription('Update investment values of investors and legal entities');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
             $output->writeln('<info>Creating a message queue for updating the investment values</info>');

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

            $output->writeln(sprintf('<error>Failed to create a message queue for updating the investment values. Error is: %s</error>', $e->getMessage()));
            $output->writeln('<info>Updating the investment values directly without a message queue.</info>');

            $this->investmentValuesManager->doUpdateInvestmentValues();
        }

        $output->writeln('<info>Done.</info>');
    }
}
