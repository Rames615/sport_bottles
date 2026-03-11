<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/politique-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legal/politique-confidentialite.html.twig');
    }

    #[Route('/conditions-utilisation', name: 'app_conditions_utilisation')]
    public function conditionsUtilisation(): Response
    {
        return $this->render('legal/conditions-utilisation.html.twig');
    }

    #[Route('/politique-cookies', name: 'app_politique_cookies')]
    public function politiqueCookies(): Response
    {
        return $this->render('legal/politique-cookies.html.twig');
    }

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions-legales.html.twig');
    }

    #[Route('/politique-retour', name: 'app_politique_retour')]
    public function politiqueRetour(): Response
    {
        return $this->render('legal/politique-retour.html.twig');
    }

    #[Route('/qui-sommes-nous', name: 'app_qui_sommes_nous')]
    public function quiSommesNous(): Response
    {
        return $this->render('legal/qui-sommes-nous.html.twig');
    }
}
