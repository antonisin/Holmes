<?php

namespace App\Controller;

use App\Entity\UserNumber;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User number controller.
 * This class is implemented as symfony controller to process and manage user's personal documents numbers. Class is
 * used to update, watch and edit personal numbers stored in the system.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
#[Route("/numbers/")]
class UserNumberController extends AbstractFOSRestController
{
    /**
     * Add new personal number.
     * This method is used to add new user document personal number into the system.
     *
     * @param Request                $request - Request to extract params to store into database.
     * @param EntityManagerInterface $manager - Doctrine entity manager to work with database (store personal number).
     *
     * @return RedirectResponse - Redirect response on any of request.
     */
    #[Route(methods: ['POST'])]
    public function post(Request $request, EntityManagerInterface $manager): RedirectResponse
    {
        $repo = $manager->getRepository(UserNumber::class);
        if (false === $request->request->has('number')) {
            $this->addFlash('error', 'No personal number provided');

            return $this->redirectToRoute('dashboard');
        }

        $number = $request->request->get('number');
        $number = preg_replace('/[^0-9\/]*/', '', $number);
        $number = explode('/', $number);

        if (count($number) == 2 && null === $repo->findOneBy(['number' => $number[0], 'year' => $number[1]])) {
            $model = new UserNumber();
            $model
                ->setUser($this->getUser())
                ->setNumber($number[0])
                ->setYear($number[1])
            ;
            $manager->persist($model);
            $manager->flush();
            $this->addFlash(
                'success',
                sprintf('Number %s/%s was successfully added', $model->getNumber(), $model->getYear())
            );
        }

        return $this->redirectToRoute('dashboard');
    }

    /**
     * Toggle personal number status.
     * This method is used to toggle to inverse state of active or inactive for user document personal number.
     *
     * @param UserNumber             $number  - Doctrine entity instance of user personal number.
     * @param EntityManagerInterface $manager - Document manager instance to update personal number info.
     *
     * @return RedirectResponse - Redirect response on any type of requests.
     */
    #[Route("/toggle/{id}")]
    public function toggle(UserNumber $number, EntityManagerInterface $manager): RedirectResponse
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if ($number->getUser()->getId() !== $this->getUser()?->getId()) {
            $this->addFlash('error', 'Not allowed to update user number');

            return $this->redirectToRoute('dashboard');
        }
        $number->setEnabled(!$number->isEnabled());
        $manager->persist($number);
        $manager->flush();
        $this->addFlash('success', sprintf(
            'Personal number %s was successful %s',
            $number->getFormatted(),
            $number->isEnabled() ? 'Enabled' : 'Disabled'
        ));

        return $this->redirectToRoute('dashboard');
    }
}