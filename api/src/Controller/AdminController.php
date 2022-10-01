<?php

namespace App\Controller;

use App\Entity\InfoNumber;
use App\Entity\Source;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Admin controller class.
 * This class is implemented as symfony controller to rule and control admin pages.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
#[
    Route('/admin', name: 'admin_'),
    IsGranted(User::ROLE_ADMIN)
]
class AdminController extends AbstractController
{
    /**
     * Admin source page.
     * This method is used to render admin page for source collection.
     *
     * @param EntityManagerInterface $manager   - Entity manager instance to work with database.
     * @param Request                $request   - Client request instance.
     * @param PaginatorInterface     $paginator - Paginator service instance.
     *
     * @return Response
     */
    #[Route('/sources', name: 'sources')]
    public function source(EntityManagerInterface $manager, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $manager->getRepository(Source::class)->createQueryBuilder('source'),
            $request->query->get('page', 1),
            20
        );

        return $this->render('admin/sources.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Admin users page.
     * This method is used to render and show admin page for users collection.
     *
     * @param EntityManagerInterface $manager   - Entity manager instance to work with database.
     * @param Request                $request   - Client request instance.
     * @param PaginatorInterface     $paginator - Paginator service instance.
     *
     * @return Response
     */
    #[Route('/users', name: 'users')]
    public function users(EntityManagerInterface $manager, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $manager->getRepository(User::class)->createQueryBuilder('user'),
            $request->query->get('page', 1),
            20
        );

        return $this->render('admin/users.html.twig', ['pagination' =>  $pagination]);
    }

    /**
     * Admin numbers page.
     * This method is used to render and show admin page for info numbers collection.
     *
     * @param EntityManagerInterface $manager   - Entity manager instance to work with database.
     * @param Request                $request   - Client request instance.
     * @param PaginatorInterface     $paginator - Paginator service instance.
     *
     * @return Response
     */
    #[Route('/numbers', name: 'numbers')]
    public function numbers(EntityManagerInterface $manager, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $manager->getRepository(InfoNumber::class)->createQueryBuilder('number'),
            $request->query->get('page', 1),
            20
        );

        return $this->render('admin/numbers.html.twig', ['pagination' =>  $pagination]);
    }
}