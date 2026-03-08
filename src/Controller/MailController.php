<?php

namespace App\Controller;

use App\Form\ContactType;            
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;  
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MailController extends AbstractController
{
    public function __construct(
        private readonly MailerService $mailerService,
    ) {}

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);   // ✅ fixed casing
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->mailerService->sendContactEmail(
                    to:      'admin@sportsbottles.fr',
                    subject: 'Nouveau message de ' . $data['name'],
                    context: [
                        'name'    => $data['name'],
                        'email'   => $data['email'],
                        'subject' => $data['subject'] ?? '',
                        'message' => $data['message'],
                    ],
                );

                $this->addFlash('success', 'Votre message a bien été envoyé !');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,           // ✅ was: '$form' (string literal, not the object)
        ]);
    }
}