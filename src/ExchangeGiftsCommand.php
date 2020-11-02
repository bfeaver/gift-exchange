<?php

namespace App;

use App\Exchanger\GiftExchanger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ExchangeGiftsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = 'app:exchange';
    private GiftExchanger $exchanger;

    public function __construct(GiftExchanger $exchanger)
    {
        $this->exchanger = $exchanger;
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('list', InputArgument::REQUIRED, 'A YAML file with a list of participants');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $participantList = Yaml::parseFile(getcwd().'/'.$input->getArgument('list'));
        if ([] === $list = $participantList['participants'] ?? []) {
            $output->writeln('<error>Participant list is empty!</error>');

            return 1;
        }
        shuffle($list);
        $participants = array_column($list, 'name');

        $assignments = $this->getAssignments($list, $participants);

        $this->logger && $this->logger->debug('Assignments generated.', ['assignments' => $assignments]);

        return 0;
    }

    private function getAssignments(array $list, array $participants, $attempts = 1)
    {
        $originalParticipants = $participants;

        try {
            $assignments = [];

            foreach ($list as $person) {
                $assignments[$person['name']] = $assignment = $this->exchanger->getAssignment(
                    $person['name'],
                    $person['exclusions'] ?? [],
                    $participants
                );
                $this->logger && $this->logger->debug(sprintf('Participant "%s" was given "%s".', $person['name'], $assignment));
                $participants = array_filter($participants, function ($v) use ($assignment) {
                    return $assignment !== $v;
                });
            }

            return $assignments;
        } catch (\InvalidArgumentException $e) {
            $this->logger && $this->logger->notice('Invalid assignments, retrying');

            if ($attempts > 10) {
                throw new \RuntimeException('Could not generate assignments in less than 10 retries');
            }

            return $this->getAssignments($list, $originalParticipants, ++$attempts);
        }
    }
}
