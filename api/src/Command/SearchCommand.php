<?php

namespace App\Command;

use App\Entity\InfoNumber;
use App\Entity\UserNumber;
use App\Service\UserNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Search personal number command.
 * This class is implemented as symfony cli command and used as command (usually for cronjob or supervisor) to search
 * user's personal numbers in the system. In case when command match/found personal number in project database,
 * notification will be sent to email or phone number.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class SearchCommand extends Command
{
    /**
     * Doctrine Entity Manager.
     * This property contain Doctrine Entity Manager service instance. Manager is used to work with database and
     * repositories (update, delete, find, insert and others).
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * User Notification service instance.
     * This property contain user notification service to send sms or email message in case of true match.
     *
     * @var UserNotificationService
     */
    private UserNotificationService $notificationService;


    /**
     * {@inheritDoc}
     *
     * @param EntityManagerInterface $manager - Doctrine entity manager to work with database.
     */
    public function __construct(EntityManagerInterface $manager, UserNotificationService $notificationService)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->notificationService = $notificationService;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:watch:search')
            ->setDescription('Command to search subscribed/watched user numbers.')
            ->setAliases(['a:w:s'])
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit of subscribed numbers used on run.'
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '900M');
        ini_set('post_max_size', '10000M');
        ini_set('upload_max_filesize', '10000M');
        ini_set('max_execution_time', '600');

        $limit = (int) ($input->getOption('limit') ?? 1);

        $collection = $this->manager->getRepository(UserNumber::class)->findBy([
                'enabled'    => true,
                'infoNumber' => null,
        ], ['searchAt' => 'ASC'], $limit);

        $repo = $this->manager->getRepository(InfoNumber::class);
        /** @var UserNumber $model */
        foreach ($collection as $model) {
            $output->write(sprintf(
                'ID: %d | Year: %d | Number: %d - ',
                $model->getId(),
                $model->getYear(),
                $model->getNumber()
            ));

            if (!$model->isEnabled()) {
                $output->writeln('Skipped [Disabled]');
                continue;
            }

            $exist = $repo->findOneBy([
                'number' => $model->getNumber(),
                'year'   => $model->getYear(),
            ]);
            if ($exist) {
                $output->writeln('Found');

                $model->setInfoNumber($exist);

                $this->notificationService
                    ->setNotification($model->getUser()->getNotification())
                    ->sendNotification($model)
                ;
            } else {
                $output->writeln('Not Found');
            }
            $model->setSearchAt(new \DateTime());
            $this->manager->persist($model);
        }
        $this->manager->flush();

        return Command::SUCCESS;
    }
}