<?php

namespace App\Command;

use App\Entity\InfoNumber;
use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Smalot\PdfParser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Source parse command.
 * This method is implemented as symfony cli command to parse already downloaded pdf source files and store all info
 * into system database.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
class SourceParseCommand extends Command
{
    public const NUMBER_INFO_REGEXP = '/\((\d{3,}\/([a-zA-Z]{1,5})?\/?\d+)\)/';


    /**
     * Container bag instance.
     * This property contain container bag instance to extract system params and env values.
     *
     * @var ContainerBag|ContainerBagInterface
     */
    private ContainerBag|ContainerBagInterface $containerBag;

    /**
     * Doctrine entity manager instance.
     * This property contain doctrine entity manager service instance used to work with database.
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;


    /**
     * {@inheritDoc}
     *
     * @param ContainerBagInterface  $containerBag - Container bug instance to extract system params and env values.
     * @param EntityManagerInterface $manager      - Doctrine entity manager service instance to work with database.
     */
    public function __construct(ContainerBagInterface $containerBag, EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->containerBag = $containerBag;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:source:parse')
            ->setDescription('Command to parse pdf sources for number/information')
            ->setAliases(['a:s:p'])
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface - Container exception on get.
     * @throws \Exception - Exception on parse pdf file.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Source $source */
        $source = $this->manager->getRepository(Source::class)->findOneBy([
            'processedAt' => null,
            'state'       => Source::STATE_OK,
        ]);
        if (null === $source) {
            return Command::SUCCESS;
        }

        /** @noinspection MissingService */
        $fileLocation = sprintf(
            '%s/public/source/%s',
            $this->containerBag->get('kernel.project_dir'),
            $source->getFileNameReal()
        );

        try {
            $parser = new Parser();
            $pdf    = $parser->parseFile($fileLocation);
        } catch (\Exception $exception) {
            $source->setState(Source::STATE_INVALID_PDF);
            $this->manager->persist($source);
            $this->manager->flush();

            return Command::SUCCESS;
        }

        $text = $pdf->getText();
        $text = preg_replace('/[^\n0-9\/()a-zA-Z]]*/', '', $text);

        preg_match_all(self::NUMBER_INFO_REGEXP, $text, $collection);
        foreach ($collection[1] as $item) {
            $temp = explode('/', $item);
            if (count($temp) < 2) {
                continue;
            }

            $model = new InfoNumber();
            $model
                ->setSource($source)
                ->setNumber($temp[0])
            ;
            if (count($temp) === 2) {
                $model->setYear($temp[1]);
            } else {
                $model
                    ->setCode($temp[1])
                    ->setYear($temp[2])
                ;
            }
            $this->manager->persist($model);
        }
        $source->setProcessedAt(new \DateTime());
        $this->manager->persist($source);
        $this->manager->flush();

        return Command::SUCCESS;
    }
}