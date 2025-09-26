<?php
// tests/Api/ApiTestCase.php
declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient(); // arranca el kernel en "test"
    }

    protected function requestJson(string $method, string $uri, array $payload = [], array $headers = []): void
    {
        $default = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT'  => 'application/json',
        ];

        // Permite añadir Authorization: Bearer ... u otros headers
        $server = array_merge($default, $headers);

        $content = $payload ? json_encode($payload, JSON_THROW_ON_ERROR) : null;

        $this->client->request($method, $uri, server: $server, content: $content);
    }

    protected function assertJsonHasPath(array $json, string $path): void
    {
        // Soporte para "user.name" o "items.0.id"
        $segments = explode('.', $path);
        $cursor = $json;
        foreach ($segments as $seg) {
            if (ctype_digit($seg)) {
                $seg = (int) $seg;
            }
            $this->assertArrayHasKey($seg, $cursor, "Falta la clave '$path'");
            $cursor = $cursor[$seg];
        }
        $this->assertTrue(true); // llegó al final: existe
    }
}
