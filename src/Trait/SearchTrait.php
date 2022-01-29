<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Trait;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;
use whatwedo\SearchBundle\Manager\SearchManager;

trait SearchTrait
{

    /**
     * @return int[]
     */
    protected function getLimitChoices(): array
    {
        return [
            25,
            50,
            100,
            200
        ];
    }

    /**
     * @return string
     */
    protected function getSearchTemplate(): string
    {
        return '@whatwedoSearch/index.html.twig';
    }

    /**
     * @param bool $useStopWatch
     * @param Request $request
     * @param SearchManager $searchManager
     * @return array
     */
    protected function getGlobalResults(Request $request, SearchManager $searchManager, bool $useStopWatch = true): array
    {
        if ($useStopWatch) {
            $stopWatch = new Stopwatch();
            $stopWatch->start('whatwedoSearch');
        }
        $searchTerm = $request->query->get('query');

        $results = $searchManager->search($searchTerm);

        $limit = $request->query->getInt('limit', 25);

        $total = count($results);
        $pages = (int)ceil($total / $limit);
        $currentPage = $request->query->getInt('page', 1);

        $results = array_slice($results, ($currentPage - 1) * $limit, $limit);

        $pagination = [
            'total' => $total,
            'totalResults' => $total,
            'totalPages' => $pages,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'offsetStart' => ($currentPage - 1) * $limit + 1,
            'offsetEnd' => ($currentPage === $pages) ? $total : $limit,
            'limit' => $limit,
            'limit_choices' => $this->getLimitChoices()
        ];

        $templateParams = [
            'results' => $results,
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'duration' => $useStopWatch ? $stopWatch->start('whatwedoSearch')->getDuration() : 0,
        ];
        return $templateParams;
    }
}
