<?php

namespace App;

use App\Exchanger\GiftExchanger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Yaml\Yaml;

class ExchangeGiftsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = 'app:exchange';
    private GiftExchanger $exchanger;
    private MailerInterface $mailer;

    public function __construct(GiftExchanger $exchanger, MailerInterface $mailer)
    {
        $this->exchanger = $exchanger;
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('list', InputArgument::REQUIRED, 'A YAML file with a list of participants')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Will not send emails and will print out assignments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $participantConfig = Yaml::parseFile(getcwd().'/'.$input->getArgument('list'));

        if ([] === $list = $participantConfig['participants'] ?? []) {
            $output->writeln('<error>Participant list is empty!</error>');

            return 1;
        }
        shuffle($list);
        $participants = array_column($list, 'name');

        $assignments = $this->getAssignments($list, $participants);

        $this->logger && $this->logger->debug('Assignments generated.', ['assignments' => $assignments]);

        foreach ($participantConfig['participants'] as $participant) {
            if (null === ($emailAddress = $participant['email'] ?? null)) {
                continue;
            }

            if ($input->getOption('dry-run')) {
                $output->writeln(sprintf('Would send email to "%s" assigned to "%s".', $participant['name'], $assignments[$participant['name']]));
                continue;
            }

            $output->writeln(sprintf('Sending email to "%s"', $participant['name']));
            $message = sprintf('You have been assigned "%s"! Do not forget the limit is $60.', $assignments[$participant['name']]);
            $email = (new Email())
                ->from($participantConfig['mailer_from'])
                ->to($emailAddress)
                ->subject('Your gift exchange assignment')
                ->text($message)
                ->html("<p>$message</p>");

            $this->mailer->send($email);
        }

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
