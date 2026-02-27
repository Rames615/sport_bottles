<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WebhookController extends AbstractController
{
    #[Route('/webhook', name: 'app_webhook')]
    public function index(): Response
    {
        return $this->render('webhook/index.html.twig', [
            'controller_name' => 'WebhookController',
        ]);
    }
}
