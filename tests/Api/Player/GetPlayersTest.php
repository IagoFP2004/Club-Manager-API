<?php

declare(strict_types=1);

namespace App\Tests\Api\Player;

use App\Tests\Api\ApiTestCase;

final class GetPlayersTest extends ApiTestCase
{
    public function test_list_players_returns_paginated_payload(): void
    {
        // 1) Hacer la petición
        $this->requestJson('GET', '/players');

        // 2) Respuesta básica
        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        // 3) Decodificar JSON una vez
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        // 4) Estructura principal
        self::assertArrayHasKey('players', $data);
        self::assertArrayHasKey('pagination', $data);
        self::assertIsArray($data['players']);
        self::assertIsArray($data['pagination']);

        // 5) Claves de paginación
        foreach ([
            'current_page', 'per_page', 'total_items', 'total_pages',
            'has_next_page', 'has_prev_page', 'next_page', 'prev_page'
        ] as $key) {
            self::assertArrayHasKey($key, $data['pagination']);
        }

        // 6) Comprobar el primer elemento si existe
        if (!empty($data['players'])) {
            $player = $data['players'][0];
            foreach (['id','nombre','apellidos','dorsal','salario','club','entrenador'] as $key) {
                self::assertArrayHasKey($key, $player, "Falta la clave '$key' en el primer jugador");
            }

            // (Opcional) tipos básicos
            self::assertIsInt($player['id']);
            self::assertIsString($player['nombre']);
        }
    }
}
