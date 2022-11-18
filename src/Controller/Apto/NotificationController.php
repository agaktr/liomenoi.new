<?php

namespace App\Controller\Apto;

use App\Entity\Apto\Notification;
use App\Form\Apto\NotificationType;
use App\Repository\Apto\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/notification")
 * @IsGranted("ROLE_ADMIN")
 */
class NotificationController extends AptoAbstractController
{
    /**
     * @Route("/", name="app_notification_index", methods={"GET"})
     */
    public function index(Request $request,NotificationRepository $notificationRepository): Response
    {

        $currentPage = $request->get('_page', 1);
        $offset = ($currentPage - 1) * self::PER_PAGE;

        $total = $notificationRepository->count([]);
        $notifications = $notificationRepository->findBy([],[],self::PER_PAGE,$offset);

        return $this->render('theme/notification/index.html.twig', [
            'notifications' =>
                [
                    'items'=>$notifications,
                    'total' => $total,
                    'perPage' => self::PER_PAGE,
                    'offset' => $offset,
                ],

        ]);
    }

    /**
     * @Route("/seen", name="app_notification_seen", methods={"POST"})
     */
    public function seen(Request $request, EntityManagerInterface $entityManager): Response
    {

        $notiId = json_decode($request->getContent(), true)['notiId'];

        $notification = $entityManager->getRepository(Notification::class)->find($notiId);

        $notification->addSeen($this->getUser());

        $entityManager->flush();

        return $this->json(
            [
                'success' => true,
                'message' => 'seen',
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/new", name="app_notification_new", methods={"GET", "POST"})
     */
    public function new(Request $request, NotificationRepository $notificationRepository): Response
    {
        $notification = new Notification();
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notificationRepository->add($notification, true);

            $this->cache->flushdb();

            return $this->redirectToRoute('app_notification_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('theme/notification/new.html.twig', [
            'notification' => $notification,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_notification_show", methods={"GET"})
     */
    public function show(Notification $notification): Response
    {
        return $this->render('theme/notification/show.html.twig', [
            'notification' => $notification,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_notification_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Notification $notification, NotificationRepository $notificationRepository): Response
    {
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notificationRepository->add($notification, true);

            return $this->redirectToRoute('app_notification_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('theme/notification/edit.html.twig', [
            'notification' => $notification,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_notification_delete", methods={"POST"})
     */
    public function delete(Request $request, Notification $notification, NotificationRepository $notificationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$notification->getId(), $request->request->get('_token'))) {
            $notificationRepository->remove($notification, true);

            $this->cache->flushdb();
        }

        return $this->redirectToRoute('app_notification_index', [], Response::HTTP_SEE_OTHER);
    }
}
