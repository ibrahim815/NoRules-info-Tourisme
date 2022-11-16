<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Reservationhotel;
use App\Entity\User;
use App\Form\ReservationhotelType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reservationhotel")
 */
class ReservationhotelController extends AbstractController
{

    /**
     * @Route("/listHotel", name="hotel", methods={"GET"})
     */
    public function hotelindex(): Response
    {
        $hotels = $this->getDoctrine()
            ->getRepository(Hotel::class)
            ->findAll();

        return $this->render('reservationhotel/indexhotel.html.twig', [
            'hotels' => $hotels,
        ]);
    }




    /**
     * @Route("/{id}", name="reservationhotel_index", methods={"GET"})
     */
    public function index($id): Response
    {

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        $reservationhotels = $this->getDoctrine()
            ->getRepository(Reservationhotel::class)
            ->findBy(array('idUser'=>$user));




        return $this->render('reservationhotel/index.html.twig', [
            'reservationhotels' => $reservationhotels,
        ]);
    }


    /**
     * @Route("/new/{id}", name="reservationhotel_new", methods={"GET","POST"})
     */
    public function new(Request $request,$id): Response
    {
        $reservationhotel = new Reservationhotel();
        $form = $this->createForm(ReservationhotelType::class, $reservationhotel);
        $form->handleRequest($request);

        $hotel = $this->getDoctrine()
            ->getRepository(Hotel::class)
            ->find($id);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reservationhotel->setIdUser($user);
            $reservationhotel->setIdHotel($hotel);
            $entityManager->persist($reservationhotel);
            $entityManager->flush();

            return $this->redirectToRoute('reservationhotel_index', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservationhotel/new.html.twig', [
            'reservationhotel' => $reservationhotel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{idRes}", name="reservationhotel_show", methods={"GET"})
     */
    public function show(Reservationhotel $reservationhotel): Response
    {
        return $this->render('reservationhotel/show.html.twig', [
            'reservationhotel' => $reservationhotel,
        ]);
    }

    /**
     * @Route("/{idRes}/edit", name="reservationhotel_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Reservationhotel $reservationhotel): Response
    {
        $form = $this->createForm(ReservationhotelType::class, $reservationhotel);
        $form->handleRequest($request);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reservationhotel_index', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservationhotel/edit.html.twig', [
            'reservationhotel' => $reservationhotel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("delete/{idh}", name="reservationhotel_delete")
     */
    public function delete($idh)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $this->getDoctrine()
            ->getRepository(Reservationhotel::class)
            ->find($idh);
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);
        $entityManager->remove($reservation);
        $entityManager->flush();


        return $this->redirectToRoute('reservationhotel_index', ['id' => $user->getId()]);
    }
}
