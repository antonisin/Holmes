<?php

namespace App\Command;

use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use MaximAntonisin\Spirit\SpiritAsyncClient;
use MaximAntonisin\Spirit\SpiritBaseClient;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\HttpFoundation\Request;
use \GuzzleHttp\Exception\ConnectException;

/**
 * Source download command.
 * This class is implemented as symfony cli command to be used in cronjob or supervisor. Command is designed to downland
 * pdf sources from remove service page and store them in public directory. Also, all downloaded sources are saved as
 * base info inside database.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.2.0
 */
class SourceDownloadCommand extends Command
{
    public const SOURCE_COLLECTION_PATH   = 'category/ordine/';
    public const SOURCE_COLLECTION_PATH_1 = 'category/ordine/page/2/';
    public const SOURCE_PDF_REGEXP = '/(http[^"]*\.pdf)">([a-zA-Z0-9]+)</';
    public const BASE_URI = 'cetatenie.just.ro/';
    public const CLIENT_HEADERS = [
        'Host'          => 'cetatenie.just.ro',
        'User-Agent'    => ' PostmanRuntime/7.29.0',
        'Postman-Token' => ' 2e973768-333c-4d3a-ac4c-2f46f6def242',
    ];

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
     * Current client request params.
     *
     * This property contain current client params used for each request (like headers, verify etc.).
     * @var array
     */
    private array $requestParams = [
        RequestOptions::HEADERS => self::CLIENT_HEADERS,
        RequestOptions::VERIFY  => false,
    ];


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
                'proxy',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Proxy used for download')

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

        if ($input->hasOption('proxy') && is_string($input->getOption('proxy'))) {
            $this->requestParams[RequestOptions::PROXY] = $input->getOption('proxy');
        } elseif ($this->containerBag->has('PROXY')) {
            /** @noinspection MissingService */
            $this->requestParams[RequestOptions::PROXY] = $this->containerBag->get('PROXY');
        }

        $collection = $this->getSourceLinks();

        foreach ($collection as $link) {
            $this->client->addRequest(Request::METHOD_GET, $link, $this->requestParams);
        }
        $this->client->sendAll();

        $content = implode('', $this->client->getContents());
        preg_match_all(self::SOURCE_PDF_REGEXP, $content, $matches);

        $limit = (int) ($input->getOption('limit') ?? 10);
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

            $this->client->addRequest(Request::METHOD_GET, $model->getFileUrl(), array_merge($this->requestParams, [
                RequestOptions::SINK => $destination,
            ]));

            $this->manager->persist($model);
            $counter++;
            if ($counter >= $limit) {
                break;
            }
        }

        $output->writeln(sprintf('Downloaded %d files', $counter));

        $this->manager->flush();

        $this->client->sendAll(true);
        /** Check all responses for invalid file or request error. */
        foreach ($this->client->getResponses() as $res) {
            /** All simple response class instances are ok and can be skipped. */
            if ($res instanceof Response) {
                continue;
            }
            /** In case of connection error, source state will be updated. */
            if ($res instanceof ConnectException) {
                $uri = $res->getRequest()->getUri();
                $model = $this->manager->getRepository(Source::class)->findOneBy([
                    'fileUrl' => sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $uri->getPath()),
                ]);

                $model->setState(Source::STATE_BAD_SOURCE);
                $this->manager->persist($model);
                $this->manager->flush();
            }
        }

        return Command::SUCCESS;
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

    /**
     * Return list of source links.
     * This method is used to get array of source links. This source are used to get and parse from there links for PDF
     * files.
     *
     * @return array
     */
    private function getSourceLinks(): array
    {
        $collection = [];
        foreach ([self::SOURCE_COLLECTION_PATH, self::SOURCE_COLLECTION_PATH_1] as $path) {
            $this->client->addRequest(url: $path, params: $this->requestParams);
            $this->client->sendAll(true);
            $crawler = new Crawler($this->client->getOneContent());

            $response = $crawler->filter('.article_content .penci-link-post');
            $response = array_map(function(Link $el) {
                return $el->getUri();
            }, $response->links());
            $collection = array_merge($collection, $response);
            $this->client->reset();
        }

        return $collection;
    }
}