<?php

namespace App\Command;

use App\Entity\InfoNumber;
use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SourceParse extends Command
{
    public const NUMBER_INFO_REGEXP = '/\((\d{3,}\/\d{1,})\)/';


    private ContainerBag $containerBag;
    private EntityManagerInterface $manager;


    public function __construct(ContainerBagInterface $containerBag, EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->containerBag = $containerBag;
    }
    protected function configure()
    {
        $this
            ->setName('app:source:parse')
            ->setDescription('Command to parse pdf sources for number/information')
            ->setAliases(['a:s:p'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var Source $source */
        $source = $this->manager->getRepository(Source::class)->findOneBy([]);

        $fileLocation = sprintf(
            '%s/public/source/%s',
            $this->containerBag->get('kernel.project_dir'),
            $source->getFileNameReal()
        );

        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile($fileLocation);

        $text = $pdf->getText();
        $text = preg_replace('/[^\n0-9\/()]]*/', '', $text);

        preg_match_all(self::NUMBER_INFO_REGEXP, $text, $collection);
        foreach ($collection[1] as $item) {
            $temp = explode('/', $item);
            $model = new InfoNumber();
            $model
                ->setNumber($temp[0])
                ->setYear($temp[1])
                ->setSource($source)
            ;
            $this->manager->persist($model);
        }
        $source->setProcessedAt(new \DateTime());
        $this->manager->persist($source);
        $this->manager->flush();

        return Command::SUCCESS;
    }
}