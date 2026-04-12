<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController
{
    #[Route('/_health', name: 'health_check', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response('OK', 200, ['Content-Type' => 'text/plain']);
    }
}