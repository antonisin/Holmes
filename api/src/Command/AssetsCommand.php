<?php

namespace App\Command;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Vendor assets command.
 * This class is implemented as symfony cli command and used to parse all assets from templates and copy(symlink) them
 * into vendor folder.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class AssetsCommand extends Command
{
    /**
     * Container bag instance to extract params.
     *
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $bag;


    /**
     * {@inheritDoc}
     *
     * @param ContainerBagInterface $bag - Container bag to extract system env params.
     */
    public function __construct(ContainerBagInterface $bag)
    {
        parent::__construct();
        $this->bag = $bag;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('assets:vendor:install')
            ->setAliases(['a:v:i'])
            ->setDescription('Parse all templates and copy/symlink all assets from vendor')
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface - Container exception on get.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new Finder();
        $fs = new Filesystem();

        /** @noinspection MissingService */
        $projectDir = $this->bag->get('kernel.project_dir');
        /** @noinspection MissingService */
        $files = $finder->files()->in($this->bag->get('twig.default_path'));

        foreach ($files as $file) {
            $content = preg_replace('/\n+/', '', $file->getContents());
            $content = preg_replace('/\040{2,}/', '', $content);
            preg_match_all('/asset\((.*)\)/U', $content, $assets);
            if (empty($assets) || empty($assets[1])) {
                continue;
            }

            foreach ($assets[1] as $asset) {
                $asset = preg_replace('/[\'"`]/', '', $asset);
                $asset = preg_replace('/^(\.*?\/+?)+/m', '', $asset);
                if (false === str_starts_with($asset, 'vendor')) {
                    continue;
                }
                $asset = preg_replace('/^(vendor\/[^\/]+)\/.+/', '$1', trim($asset, '/'));
                $fs->remove(sprintf('%s/public/%s', $projectDir, $asset));
                $fs->symlink(
                    sprintf('%s/%s', $projectDir, $asset),
                    sprintf('%s/public/%s', $projectDir, $asset)
                );
            }
        }

        return Command::SUCCESS;
    }
}