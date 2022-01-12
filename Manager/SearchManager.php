<?php

namespace whatwedo\SearchBundle\Manager;

use whatwedo\SearchBundle\Repository\IndexRepository;

class SearchManager
{
    public function __construct(
        private IndexRepository $indexRepository
    )
    {
    }

    public function search(string $searchTerm) {
        return $this->indexRepository->search($searchTerm);
    }
}
