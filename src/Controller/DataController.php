<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Attribute\Route;

final class DataController extends AbstractController
{
    #[Route('/data', name: 'app_data')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DataController.php',
        ]);
    }

    #[Route('/data/items', name: 'app_data')]
    public function fetchItems(): JsonResponse
    {
        $client = HttpClient::create();

        $url = 'https://wiki.leagueoflegends.com/en-us/Module:ItemData/data';

        $response = $client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Impossible de récupérer la page');
        }

        $htmlContent = html_entity_decode($response->getContent());

        $patternStats = '/<pre>\s*(.*?)\s*<\/pre>/s';
        $patternData = '/return\s*{(.*?)\n}/s';

        preg_match_all($patternStats, $htmlContent, $matchesStats);
        preg_match_all($patternData, $htmlContent, $matchesData);

        $result = $matchesData[1][0];
        $result = preg_replace('/  +/', ' ', $result);
        $result = preg_replace('/= {"/', '= ["', $result);
        $result = preg_replace('/"},/', '"],', $result);
        $result = preg_replace('/",},/', '"],', $result);
        $result = preg_replace('/\t/', '', $result);
        $result = preg_replace('/"]=/', '"] =', $result);

        $result = preg_replace('/\[(.*)\]\s\=\s/', '$1:', $result);
        $result = preg_replace('/[\t\r\n]/', '', $result);
        $result = preg_replace('/\}\,\s--\s\[\d+\]\}/', ']]', $result);
        $result = preg_replace('/\,\s--\s\[\d+\]/', ',', $result);

        $result = preg_replace('/,(\}|\])/', '$1', $result);
        $result = preg_replace('/\}\,\{/', '],[', $result);

        $result = preg_replace('/\{\{/', '[[', $result);
        $result = preg_replace('/, }/', '}', $result);

        $result = preg_replace('/,  }/', '}', $result);
        $result = preg_replace('/\[\[/', '{{', $result);
        $result = preg_replace('/]]/', '}}', $result);
        $result = preg_replace('/\'/', '\'\'', $result);

        $result = preg_replace('/EPGP_DB\s\=/', '', $result);

        $result = substr($result, 0, -1);
        $result = "{" . $result . "}";
       
        $result = json_decode($result, true);
        dump($result["Tear of the Goddess"]["effects"]["pass"]["name"]);
        return new JsonResponse($result);
    }
}
