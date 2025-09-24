<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailController extends AbstractController
{
    /**
     * Envía un correo usando Mailer de Symfony
     */
    #[Route('/send-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(MailerInterface $mailer, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        $to = $data['to'] ?? null;
        $subject = $data['subject'] ?? 'Sin asunto';
        $message = $data['message'] ?? 'Sin mensaje';

        if (!$to) {
            return $this->json(['error' => 'El campo "to" es requerido'], 400);
        }

        try {
            $email = (new Email())
                ->from('noreply@tudominio.com')
                ->to($to)
                ->subject($subject)
                ->text($message)
                ->html('<p>' . $message . '</p>');

            $mailer->send($email);
            
            return $this->json(['message' => 'Correo enviado correctamente']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al enviar correo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Envía un correo usando la función mail() de PHP
     */
    #[Route('/send-email-basic', name: 'send_email_basic', methods: ['POST'])]
    public function sendEmailBasic(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        $to = $data['to'] ?? null;
        $subject = $data['subject'] ?? 'Sin asunto';
        $message = $data['message'] ?? 'Sin mensaje';

        if (!$to) {
            return $this->json(['error' => 'El campo "to" es requerido'], 400);
        }

        $headers = [
            'From: noreply@tudominio.com',
            'Reply-To: noreply@tudominio.com',
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/html; charset=UTF-8'
        ];

        if (mail($to, $subject, $message, implode("\r\n", $headers))) {
            return $this->json(['message' => 'Correo enviado correctamente']);
        } else {
            return $this->json(['error' => 'Error al enviar correo'], 500);
        }
    }
}
