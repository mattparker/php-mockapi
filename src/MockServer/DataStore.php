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

        $this->createLogFilesIfTheyDoNotExist($filelocation);

    }


    /**
     * @param $data
     */
    public function append ($data) {
        $this->writeData($this->filelocation, $data);
        $this->writeData($this->filelocation . '.log', $data);
    }


    /**
     * @param null $location
     * @param bool $log
     * @return array
     */
    public function fetch ($location = null, $log = false) {
        $filelocation = $this->filelocation;
        if ($log === true) {
            $filelocation .= '.log';
        }
        $data = unserialize(file_get_contents($filelocation));
        $resp = [];

        if ($location === 'last' && count($data) > 0) {
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
    public function clear ($filename = '') {
        if (!$filename) {
            $filename = $this->filelocation;
        }
        file_put_contents($filename, serialize([]));
    }

    /**
     * @param $filelocation
     */
    protected function createLogFilesIfTheyDoNotExist ($filelocation) {
        if (!file_exists($filelocation)) {
            $this->clear();
        }
        if (!file_exists($filelocation . '.log')) {
            $this->clear($filelocation . '.log');
        }
    }

    /**
     * @param $filelocation
     * @param $data
     * @return array|mixed
     */
    protected function writeData ($filelocation, $data) {
        $current = unserialize(file_get_contents($filelocation));
        $current[] = $data;
        file_put_contents($filelocation, serialize($current));
    }
} 