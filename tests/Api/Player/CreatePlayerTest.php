<?php

declare(strict_types=1);

namespace App\Tests\Api\Player;

use App\Tests\Api\ApiTestCase;

final class CreatePlayerTest extends ApiTestCase
{
    public function test_create_player_returns_201_and_payload(): void
    {
        // Datos de prueba para crear un jugador
        $payload = [
            'nombre'    => 'Lionel',
            'apellidos' => 'Messi',
            'dorsal'    => 10,
            'salario'   => 1000000,
            'club'      => 'Inter Miami',
            'entrenador'=> 'Tata Martino',
        ];

        // Hacemos la petición POST
        $this->requestJson('POST', '/players', $payload);

        $response = $this->client->getResponse();
        self::assertSame(201, $response->getStatusCode(), $response->getContent());

        // Decodificamos la respuesta
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Comprobamos que tiene las claves mínimas
        foreach (['id', 'nombre', 'apellidos', 'dorsal', 'salario', 'club', 'entrenador'] as $key) {
            self::assertArrayHasKey($key, $data, "Falta la clave '$key' en la respuesta");
        }

        // Validaciones básicas
        self::assertEquals('Lionel', $data['nombre']);
        self::assertEquals(10, $data['dorsal']);
    }

    public function test_create_player_with_invalid_payload_returns_400(): void
    {
        // Payload inválido (nombre vacío y sin club)
        $payload = [
            'nombre'    => '',
            'apellidos' => 'SinNombre',
        ];

        $this->requestJson('POST', '/players', $payload);

        $response = $this->client->getResponse();
        self::assertSame(400, $response->getStatusCode(), $response->getContent());

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('nombre', $data['errors']);
    }
}
