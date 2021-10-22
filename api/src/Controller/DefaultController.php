<?php

namespace App\Controller;

use App\Entity\UserInfoNumber;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DefaultController
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 * @version 1.0.0
 */
class DefaultController extends AbstractController
{
    #[Route("/")]
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(UserInfoNumber::class);
        if ($request->request->has('number')) {
            $number = $request->request->get('number');
            $number = preg_replace('/[^0-9\/]*/', '', $number);
            $number = explode('/', $number);

            if (count($number) == 2 && null === $repo->findOneBy(['number' => $number[0], 'year' => $number[1]])) {
                $model = new UserInfoNumber();
                $model
                    ->setUser($this->getUser())
                    ->setNumber($number[0])
                    ->setYear($number[1])
                ;
                $this->getDoctrine()->getManager()->persist($model);
                $this->getDoctrine()->getManager()->flush();

            }

        }
        return $this->render('dashboard.html.twig', [
            'user'       => $this->getUser(),
            'collection' => $repo->findBy(['user' => $this->getUser()])
        ]);
    }

}
