<?php

namespace App\Command;

use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\RequestOptions;
use MaximAntonisin\Spirit\SpiritAsyncClient;
use MaximAntonisin\Spirit\SpiritBaseClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;

class SourceDownload extends Command
{
    public const SOURCE_PATH = 'ordine-articolul-11/';
    public const SOURCE_PDF_REGEXP = '/(http[^"]*\.pdf)">([a-zA-Z0-9]{1,})</';
    public const BASE_URI = 'cetatenie.just.ro/';


    private ContainerBag $containerBag;
    private EntityManagerInterface $manager;
    private SpiritBaseClient $client;


    public function __construct(ContainerBagInterface $containerBag, EntityManagerInterface $manager)
    {
        parent::__construct();
        $this->manager      = $manager;
        $this->containerBag = $containerBag;

        $this->client = new SpiritAsyncClient([
            'base_uri' => self::BASE_URI,
        ]);
    }

    protected function configure()
    {
        $this
            ->setName('app:source:download')
            ->setDescription('Command to download all pdf sources')
            ->setAliases(['a:s:d'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client->addRequest(Request::METHOD_GET, self::SOURCE_PATH, []);
        $this->client->sendAll();
        $content = $this->getOneContent();
        preg_match_all(self::SOURCE_PDF_REGEXP, $content, $matches);

        foreach ($matches[1] as $index => $url) {
            $model = new Source();
            $model
                ->setFileUrl($url)
                ->setFileName($matches[2][$index])
                ->setFileNameReal(self::extractFileName($model->getFileUrl()));

            $destination = sprintf(
                '%s/public/source/%s',
                $this->containerBag->get('kernel.project_dir'),
                $model->getFileNameReal()
            );
            if (file_exists($destination)) {
                continue;
            }

            $this->client->addRequest(Request::METHOD_GET, $model->getFileUrl(), [
                RequestOptions::SINK => $destination,
            ]);
            $this->client->sendAll();

            $this->manager->persist($model);
            $this->manager->flush();
            die;
        }

        return Command::SUCCESS;
    }


    private function getOneContent()
    {
        foreach ($this->client->getContents() as $content) {
            if ($content and !empty($content)) {
                return $content;
            }
        }

        return false;
    }

    private function extractFileName(string $url): string
    {
        preg_match('/\/{1}([^\/]*\.pdf)$/', $url, $match);

        return $match[1];
    }
}