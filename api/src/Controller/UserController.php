<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User controller class.
 * This class is implemented as symfony controller to render and process all pages and actions related for user. Also,
 * this controller contains methods to work with user settings.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class UserController extends AbstractController
{
    /**
     * Render user settings page.
     * This method is used to render user settings page (settings for notification, phone number, email etc.).
     *
     * @return Response
     */
    #[Route("settings", name: 'app_user_settings')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', ['user' => $this->getUser()]);
    }
}