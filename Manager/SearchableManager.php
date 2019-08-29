<?php


namespace whatwedo\SearchBundle\Manager;


use whatwedo\SearchBundle\Discovery\SearchableDiscovery;

class SearchableManager
{
    /**
     * @var SearchableDiscovery
     */
    private $discovery;


    public function __construct(SearchableDiscovery $discovery)
    {
        $this->discovery = $discovery;
    }

    /**
     * Returns a list of available workers.
     *
     * @return array
     */
    public function getSearchables() {
        return $this->discovery->getSearchables();
    }

    /**
     * Returns one worker by name
     *
     * @param $name
     * @return array
     *
     * @throws \Exception
     */
    public function getSearchable($name) {
        $seachables = $this->discovery->getSeachables();
        if (isset($seachables[$name])) {
            return $seachables[$name];
        }

        throw new \Exception('Searchable not found.');
    }

    /**
     * Creates a worker
     *
     * @param $name
     * @return WorkerInterface
     *
     * @throws \Exception
     */
    public function create($name) {
        $workers = $this->discovery->getSeachables();
        if (array_key_exists($name, $workers)) {
            $class = $workers[$name]['class'];
            if (!class_exists($class)) {
                throw new \Exception('Worker class does not exist.');
            }
            return new $class();
        }

        throw new \Exception('Worker does not exist.');
    }

}
