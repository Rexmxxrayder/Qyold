<?php

namespace App\Controller;

use MongoDB\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): JsonResponse
    {
        $mongoClient = new Client('mongodb://localhost:27017');

        $collection = $mongoClient->selectCollection('Qyold', 'Items'); 

        $items = $collection->find([
            'name' => 'Black Cleaver',
        ]);

        return $this->json($items);
    }
}
