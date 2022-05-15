<?php

namespace App\Controller;

use App\Entity\UserNumber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default controller class.
 * This class is implemented as symfony controller to describe and process default requests (like dashboard page).
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class DefaultController extends AbstractController
{
    /**
     * Render dashboard page.
     * This method is used to render user dashboard page.
     *
     * @param EntityManagerInterface $manager - Doctrine entity manger instance to work with database records.
     *
     * @return RedirectResponse|Response - Response types. Redirect if current user not exist or not authenticated user.
     */
    #[Route("/", name: 'dashboard')]
    public function indexAction(EntityManagerInterface $manager): RedirectResponse|Response
    {
        if (null === $this->getUser()) {
            return $this->render('index.html.twig');
//            return $this->redirectToRoute('login');
        }

        return $this->render('dashboard/index.html.twig', [
            'user'       => $this->getUser(),
            'collection' => $manager->getRepository(UserNumber::class)
                ->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']),
        ]);
    }
}
