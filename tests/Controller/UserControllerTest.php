<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;

    private $entityManager;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        $this->markTestSkipped();
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testRegisterUser(): void
    {
        $this->markTestSkipped();
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => 'newuser@example.com',
                'password' => 'password123',
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testLoginUser(): void
    {
        $this->markTestSkipped();
        $this->client->request(
            'POST',
            '/api/login_check', // LexikJWTAuthenticationBundle default endpoint
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'username' => 'test@example.com',
                'password' => 'test',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data, 'Response should contain a JWT token');
        $this->assertNotEmpty($data['token'], 'JWT token should not be empty');
    }
}
