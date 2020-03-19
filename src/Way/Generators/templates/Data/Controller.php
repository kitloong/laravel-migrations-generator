<?php namespace Way\Generators\Templates\Data;

class Controller {

    /**
     * The name of the controller to generate.
     *
     * @var string
     */
    private $controllerName;

    /**
     * Create a new Controller template data instance.
     *
     * @param $controllerName
     */
    public function __construct($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * Fetch the template data for the controller.
     *
     * @return array
     */
    public function fetch()
    {
        return [
            'name' => $this->getName($this->controllerName),
            'collection' => $this->getCollection(),
            'resource' => $this->getResource(),
            'model' => $this->getModel(),
            'namespace' => $this->getNamespace()
        ];
    }

    /**
     * Format the name of the controller.
     *
     * @return string
     */
    private function getName()
    {
        return ucwords($this->controllerName); // LessonsController
    }

    /**
     * Format the name of the collection.
     *
     * @return string
     */
    private function getCollection()
    {
        return strtolower(str_replace('Controller', '', $this->getName())); // lessons
    }

    /**
     * Format the name of the single resource.
     *
     * @return string
     */
    private function getResource()
    {
        return str_singular($this->getCollection()); // lesson
    }

    /**
     * Format the name of the model.
     *
     * @return string
     */
    private function getModel()
    {
        return ucwords($this->getResource()); // Lesson
    }

    /**
     * Format the name of the namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return 'App\Http\Controllers';
    }

} 