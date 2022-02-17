<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Trait;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Stopwatch\Stopwatch;
use whatwedo\SearchBundle\Manager\SearchManager;
use whatwedo\SearchBundle\Model\ResultItem;

trait SearchTrait
{
    private array $searchOptions = [];

    /**
     * @return int[]
     */
    protected function getLimitChoices(): array
    {
        return [
            25,
            50,
            100,
            200,
        ];
    }

    protected function getSearchTemplate(): string
    {
        return '@whatwedoSearch/index.html.twig';
    }

    protected function getGlobalResults(Request $request, SearchManager $searchManager, $options = []): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefault(SearchOptions::OPTION_ENTITY_ORDER, []);
        $resolver->setDefault(SearchOptions::OPTION_ENTITIES, []);
        $resolver->setDefault(SearchOptions::OPTION_GROUPS, []);
        $resolver->setDefault(SearchOptions::OPTION_STOP_WATCH, false);

        $this->searchOptions = $resolver->resolve($options);

        if ($this->searchOptions[SearchOptions::OPTION_STOP_WATCH]) {
            $stopWatch = new Stopwatch();
            $stopWatch->start('whatwedoSearch');
        }
        $searchTerm = $request->query->get('query');

        $results = $searchManager->searchByEntites(
            $searchTerm,
            $this->searchOptions[SearchOptions::OPTION_ENTITIES],
            $this->searchOptions[SearchOptions::OPTION_GROUPS]
        );

        $results = $this->orderResults($results);

        $limit = $request->query->getInt('limit', 25);

        $total = count($results);
        $pages = (int) ceil($total / $limit);
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
            'limit_choices' => $this->getLimitChoices(),
        ];

        $templateParams = [
            'results' => $results,
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'duration' => $this->searchOptions[SearchOptions::OPTION_STOP_WATCH] ? $stopWatch->start('whatwedoSearch')->getDuration() : 0,
        ];

        return $templateParams;
    }

    private function orderResults(array $results): array
    {
        if (count($this->searchOptions[SearchOptions::OPTION_ENTITY_ORDER]) > 0) {
            $reorder = [];

            foreach ($results as $result) {
                if (in_array($result->getClass(), $this->searchOptions[SearchOptions::OPTION_ENTITY_ORDER], true)) {
                    $reorder[array_search($result->getClass(), $this->searchOptions[SearchOptions::OPTION_ENTITY_ORDER], true)][] = $result;
                } else {
                    $reorder[999][] = $result;
                }
            }

            ksort($reorder);

            // flatten
            $results = [];
            foreach ($reorder as $reorderEntity) {
                foreach ($reorderEntity as $item) {
                    $results[] = $item;
                }
            }
        } else {
            usort($results, fn (ResultItem $a, ResultItem $b) => $a->getScore() - $b->getScore());
        }

        return $results;
    }
}
