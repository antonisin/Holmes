<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to clean application.
 * This class is implemented as symfony cli command and used as command (usually for cronjob or supervisor) to clean
 * application from useless data and temp users.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class CleanCommand extends Command
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
     * {@inheritDoc}
     *
     * @param EntityManagerInterface $manager - Doctrine entity manager to work with database.
     */
    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:clean')
            ->setDescription('Command to clean useless and temp data. Clean application.')
            ->setAliases(['a:c'])
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

        $qb = $this->manager->getRepository(User::class)->createQueryBuilder('u');
        $qb
            ->where($qb->expr()->like('u.roles', ':role'))
            ->setParameter('role', sprintf('%%%s%%', User::ROLE_TEMP_USER))
            ->delete()
            ->getQuery()->execute()
        ;

        return Command::SUCCESS;
    }
}