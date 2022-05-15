<?php

namespace App\Command;

use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\RequestOptions;
use JetBrains\PhpStorm\Pure;
use MaximAntonisin\Spirit\SpiritAsyncClient;
use MaximAntonisin\Spirit\SpiritBaseClient;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Source download command.
 * This class is implemented as symfony cli command to be used in cronjob or supervisor. Command is designed to downland
 * pdf sources from remove service page and store them in public directory. Also, all downloaded sources are saved as
 * base info inside database.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class SourceDownloadCommand extends Command
{
    public const SOURCE_PATH = 'ordine-articolul-11/';
    public const SOURCE_PDF_REGEXP = '/(http[^"]*\.pdf)">([a-zA-Z0-9]+)</';
    public const BASE_URI = 'cetatenie.just.ro/';


    /**
     * Container bag instance.
     * This property contain container bag service instance used to get project params and constants.
     *
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $containerBag;

    /**
     * Doctrine Entity Manager.
     * This property contain Doctrine Entity Manager service instance. Manager is used to work with database and
     * repositories (update, delete, find, insert and others).
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * Spirit client instance.
     * This property contain an instance of spirit client (guzzle wrapper/abstraction). Client is used to send requests.
     *
     * @var SpiritAsyncClient|SpiritBaseClient
     */
    private SpiritBaseClient|SpiritAsyncClient $client;


    /**
     * {@inheritDoc}
     *
     * @param ContainerBagInterface  $containerBag - Container bag instance with all project params and constants.
     * @param EntityManagerInterface $manager      - Doctrine entity manager to work with database.
     */
    public function __construct(ContainerBagInterface $containerBag, EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->manager      = $manager;
        $this->containerBag = $containerBag;

        $this->client = new SpiritAsyncClient(['base_uri' => self::BASE_URI]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:source:download')
            ->setDescription('Command to download all pdf sources')
            ->setAliases(['a:s:d'])
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit number of downloaded files per run.'
            )
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface - Container exception on get.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '900M');
        ini_set('post_max_size', '10000M');
        ini_set('upload_max_filesize', '10000M');
        ini_set('max_execution_time', '600');

        $this->client->addRequest(Request::METHOD_GET, self::SOURCE_PATH, []);
        $this->client->sendAll();
        $content = $this->getOneContent();
        preg_match_all(self::SOURCE_PDF_REGEXP, $content, $matches);

        $limit = (int) ($input->getOption('limit') ?? 999999);
        $counter = 0;

        foreach ($matches[1] as $index => $url) {
            $model = new Source();
            $model
                ->setFileUrl($url)
                ->setFileName($matches[2][$index])
                ->setFileNameReal(self::extractFileName($model->getFileUrl()));

            if (!empty($this->manager->getRepository(Source::class)->findOneBy(['fileUrl' => $model->getFileUrl()]))) {
                continue;
            }
            /** @noinspection MissingService */
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
            $counter++;
            if ($counter >= $limit) {
                break;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Get first content from responses.
     * Async Spirit client is using several requests to get content and as result store their responses in collection.
     * This method will return first successful response from collection of responses.
     *
     * @return false|array|mixed
     */
    #[Pure]
    private function getOneContent(): mixed
    {
        foreach ($this->client->getContents() as $content) {
            if ($content and !empty($content)) {
                return $content;
            }
        }

        return false;
    }

    /**
     * Extract file name from url.
     * This method will extract pdf file name from url.
     *
     * @param string $url - Url to search for filename
     *
     * @return string
     */
    private function extractFileName(string $url): string
    {
        preg_match('/\/([^\/]*\.pdf)$/', $url, $match);

        return $match[1];
    }
}