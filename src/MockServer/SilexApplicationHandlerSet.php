<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:19
 */

namespace MockServer;


use stdClass;

class SilexApplicationHandlerSet implements \Iterator {


    /**
     * @var String
     */
    protected $route;

    /**
     * @var SilexApplicationHandler[]
     */
    protected $handlers;


    /**
     * @var int
     */
    protected $position = 0;


    /**
     * @param $route
     * @param $definitions
     */
    public function __construct ($route, $definitions) {

        $this->route = $route;
        $this->position = 0;
        $this->createHandlers($definitions);

    }


    /**
     * @return String
     */
    public function getRoute () {
        return $this->route;
    }


    /**
     * @param stdClass $definitions
     */
    protected function createHandlers (stdClass $definitions) {
        foreach ($definitions as $method => $definition) {

            $route = new SilexApplicationHandler($method, $definition);
            $this->handlers[] = $route;
        }
    }



    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->handlers[$this->position];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->handlers[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
    }


}