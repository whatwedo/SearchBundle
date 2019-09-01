<?php
namespace whatwedo\SearchBundle\Discovery;


use whatwedo\SearchBundle\Annotation\Searchable;

class SearchableDiscovery
{

    use WorkerBundle\Annotation\Worker;
    use Doctrine\Common\Annotations\Reader;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Symfony\Component\HttpKernel\Config\FileLocator;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * The Kernel root directory
     * @var string
     */
    private $rootDir;

    /**
     * @var array
     */
    private $searchables = [];



    /**
     * SearchableDiscovery constructor.
     *
     * @param $namespace
     *   The namespace of the workers
     * @param $directory
     *   The directory of the workers
     * @param $rootDir
     * @param Reader $annotationReader
     */
    public function __construct($namespace, $directory, $rootDir, Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->rootDir = $rootDir;
    }

    /**
     * Returns all the workers
     */
    public function getSearchables() {
        if (!$this->searchables) {
            $this->discoverSearchables();
        }

        return $this->searchables;
    }

    /**
     * Discovers workers
     */
    private function discoverSearchables() {
        $path = $this->rootDir . '/../src/';
        $finder = new Finder();
        $finder->files()->in($path);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class ='\\' . $file->getBasename('.php');
            $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), Searchable::class);
            if (!$annotation) {
                continue;
            }

            /** @var Worker $annotation */
            $this->seachables[$annotation->getName()] = [
                'class' => $class,
                'annotation' => $annotation,
            ];
        }
    }

}
