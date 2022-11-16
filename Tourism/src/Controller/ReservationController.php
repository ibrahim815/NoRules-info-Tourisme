<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Voiture;
use App\Form\ReservationType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @Route("/reservationVoiture")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="reservation_index", methods={"GET"})
     */
    public function index(Request $request , PaginatorInterface $paginator): Response
    {

        $donnees = $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->findBy(array("etat"=>"En Cours"));;

        $reservations =$paginator->paginate(
            $donnees, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }



    /**
     * @Route("/useraffiche/{id}", name="reservation_useraffiche", methods={"GET"})
     */
    public function userAffiche($id): Response
    {
        $reservations = $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->findBy(array("idUser"=>$id));

        return $this->render('reservation/indexuser.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * @Route("/new", name="reservation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setEtat("En cours");
            $reservation->setDateres(new \DateTime('now'));
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find(1);
            $voiture = $this->getDoctrine()
                ->getRepository(Voiture::class)
                ->find(1);
            $reservation->setIdUser($user);
            $reservation->setIdVoiture($voiture);
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('reservation_useraffiche', ['id'=>$user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{idres}", name="reservation_show", methods={"GET"})
     */
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }



    /**
     * @Route("pdfreservation/{idres}", name="reservation_pdf", methods={"GET"})
     */
    public function pdfShow($idres): Response
    {

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $reservation = $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->find($idres);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('reservation/reservationPdf.html.twig', [
            'reservation' => $reservation,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);

        }

    /**
     * @Route("/{idres}/edit", name="reservation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Reservation $reservation): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reservation_useraffiche', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("delete/{idres}", name="reservation_delete")
     */
    public function delete($idres)
    {
            $entityManager = $this->getDoctrine()->getManager();
            $reservation = $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->find($idres);
             $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);
            $entityManager->remove($reservation);
            $entityManager->flush();


        return $this->redirectToRoute('reservation_useraffiche', ['id' => $user->getId()]);
    }


    /**
     * @Route("/accepter/{idres}", name="reservation_accept", methods={"GET","POST"})
     */
    public function accepter(Request $request,  $idres): Response
    {

        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->find($idres);
        $reservation->setEtat("Accepté");
        $entityManager->flush();
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('reservation_index');
        }


    /**
     * @Route("/refuse/{idres}", name="reservation_refuse", methods={"GET","POST"})
     */
    public function refuse(Request $request,  $idres): Response
    {

        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $this->getDoctrine()
            ->getRepository(Reservation::class)
            ->find($idres);
        $reservation->setEtat("Refusé");
        $entityManager->flush();
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('reservation_index');
    }


}
