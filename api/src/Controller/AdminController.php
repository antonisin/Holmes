<?php

namespace App\Controller;

use App\Entity\Source;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Admin controller class.
 * This class is implemented as symfony controller to rule and control admin pages.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Render main admin page.
     * This method is used to render main admin page.
     *
     * @param EntityManagerInterface $manager - Entity manager service instance to work with database.
     *
     * @return Response
     */
    #[Route('', name: 'admin')]
    public function index(EntityManagerInterface $manager): Response
    {
        return $this->render('admin/index.html.twig', [
            'sources' => $manager->getRepository(Source::class)->findAll(),
            'users'   => $manager->getRepository(User::class)->findAll(),

        ]);
    }
}