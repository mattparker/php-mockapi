<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 04/07/14
 * Time: 09:28
 */

namespace MockServer;


/**
 * Class DataStore
 *
 * Simple file-based storage for response/request objects
 *
 * @package MockServer
 */
class DataStore {

    /**
     * @var
     */
    protected $filelocation;


    /**
     * @param $filelocation
     */
    public function __construct ($filelocation) {
        $this->filelocation = $filelocation;
    }


    /**
     * @param $data
     */
    public function append ($data) {
        $current = unserialize(file_get_contents($this->filelocation));
        $current[] = $data;
        file_put_contents($this->filelocation, serialize($current));

    }


    /**
     * @param null $location
     * @return array
     */
    public function fetch ($location = null) {
        $data = unserialize(file_get_contents($this->filelocation));
        $resp = [];

        if ($location === 'last') {
            $resp = $data[count($data) -1];
        } else if ($location === 'all') {
            $resp = $data;
        } else if ($location !== '' && $location >= 0 && array_key_exists($location, $data)) {
            $resp = $data[$location];
        }

        return $resp;

    }


    /**
     * Clears out all existing data (and puts an empty array in)
     */
    public function clear () {
        file_put_contents($this->filelocation, serialize([]));
    }
} 