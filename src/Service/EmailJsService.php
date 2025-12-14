<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailJsService
{
    private HttpClientInterface $httpClient;
    private string $serviceId;
    private string $templateId;
    private string $userId;

    public function __construct(
        HttpClientInterface $httpClient,
        string $emailjsServiceId,
        string $emailjsTemplateId,
        string $emailjsUserId
    ) {
        $this->httpClient = $httpClient;
        $this->serviceId = $emailjsServiceId;
        $this->templateId = $emailjsTemplateId;
        $this->userId = $emailjsUserId;
    }

    public function sendDossierReadyEmail(array $params): void
    {
        $response = $this->httpClient->request('POST', 'https://api.emailjs.com/api/v1.0/email/send', [
            'json' => [
                'service_id' => $this->serviceId,
                'template_id' => $this->templateId,
                'user_id' => $this->userId,
                'template_params' => $params
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("EmailJS API returned: " . $response->getStatusCode());
        }
    }
}