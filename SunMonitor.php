<?php

/**
 * SunMonitor Class
 *
 * @category  Server Monitoring
 * @package   SunMonitor
 * @author    Mehmet Selcuk Batal <batalms@gmail.com>
 * @copyright Copyright (c) 2024, Sunhill Technology <www.sunhillint.com>
 * @license   https://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0
 * @link      https://github.com/msbatal/PHP-Server-Monitoring-Class
 * @version   1.3.7
 */

class SunMonitor
{
    /**
     * Array that holds server ips/domains
     * @var array
     */
    private $hostList = [];

    /**
     * Array that holds server names
     * @var array
     */
    private $nameList = [];

    /**
     * Array that holds log records
     * @var array
     */
    private $recordList = [];

    /**
     * Array that holds server statuses
     * @var array
     */
    private $serverStatus = [];

    /**
     * Logging to file (on/off)
     * @var boolean
     */
    private $fileOutput = false;

    /**
     * Number of ping attempt
     * @var integer
     */
    protected $attempt = 2;

    /**
     * @param string|array $hosts
     * @param string|array $names
     * @param boolean $output
     */
    public function __construct($hosts = null, $names = null, $output = false) {
        set_exception_handler(function($exception) {
            echo '<b>[SunClass] Exception:</b> ' . $exception->getMessage();
        });
        if (is_array($hosts)) { // assign ip addresses/domains to the array
            $this->hostList = $hosts;
        } else { // insert ip address/domain into the array
            $this->hostList[] = $hosts;
        }
        if (is_array($names)) { // assign server names to the array
            $this->nameList = $names;
        } else { // insert server name into the array
            $this->nameList[] = $names;
        }
        $this->fileOutput = $output;
        $this->validate();
        return $this;
    }

    /**
     * Validate input values
     * 
     * @throws exception
     */
    private function validate() {
        if (count($this->hostList) == 0) {
            throw new \Exception('Ip Address/Domain is not set.');
        }
        $this->validateHost(); // ip/domain validation
        if (count($this->hostList) != count($this->nameList)) {
            $this->validateName(); // server name validation
        }
    }

    /**
     * Validate host ips and/or domains
     */
    private function validateHost() {
        foreach ($this->hostList as $key => $value) {
            if (filter_var($value, FILTER_VALIDATE_IP) == true) {
                continue;
            } else {
                if (filter_var($value, FILTER_VALIDATE_DOMAIN) == true) {
                    $value = preg_replace('/https?:\/\/|www.|\/$/', '', $value); // remove protocol and subdomain
                    if (substr($value, -1) != '.') {
                        $value .= '.'; // add dot (.) end of domain
                    }
                    $ipAddress = gethostbyname($value); // get ip address from domain
                    //$dnsA = dns_get_record($value, DNS_A); // get ip address from dns record
		            //$ipAddress = $dnsA[0]['ip'];
                    $this->hostList[$key] = $ipAddress;
                } else {
                    unset($this->hostList[$key]);
                }
            }
        }
    }

    /**
     * Validate server names (alias)
     */
    private function validateName() {
        $empty = count($this->hostList) - count($this->nameList);
        for ($i = 1; $i <= $empty; $i++) {
            $this->nameList[] = 'Noname Server #' . $i; // assign server name if not set by admin
        }
    }

    /**
     * Create log file and save ping results
     * 
     * @throws exception
     */
    private function saveLog() {
        if ($this->fileOutput != true) {
            throw new \Exception('Logging is turned off in settings.');
        }
        $jsonFile = 'logs/' . date('Ymd') . '.json'; // json file location and name
        if (file_exists($jsonFile)) { // update file with new records (if exists) 
            $content = file_get_contents($jsonFile);
            $jsonTemp = json_decode($content, true);
            array_push($jsonTemp, $this->serverStatus);
            $jsonData = json_encode($jsonTemp);
            file_put_contents($jsonFile, $jsonData);
        } else { // create file with the records (if not exists) 
            $jsonData = '[' . json_encode($this->serverStatus) . ']';
            $file = fopen($jsonFile, 'a+');
            fwrite($file, $jsonData);
            fclose($file);
        }
    }

    /**
     * Monitor servers and save results to log file
     */
    public function monitor() {
        $content = [];
        $counter = 0;
        $this->serverStatus = [];
        foreach ($this->hostList as $host) {
            $pingreply = exec("ping -c $this->attempt $host", $output, $result); // ping process
            if (substr($pingreply, -2) == 'ms' || $result == '0') { // if reach server (successful)
                $speed = explode("/", $pingreply);
                $speed = $speed[4]." ms";
                $status = "Available";
            } else { // if can't reach server (unsuccessful)
                $speed = "Timeout";
                $status = "Unavailable";
            }
            $this->serverStatus[] = ["datetime" => date("Y-m-d H:i:s"), "ip" => $host, "name" => $this->nameList[$counter], "status" => $status, "speed" => $speed, "reply" => $pingreply]; // ping result
            $counter++;
        }
        if ($this->fileOutput == true) {
            $this->saveLog(); // save result to log file
        }
        return $this;
    }

    /**
     * Print records by reading log file
     * 
     * @param string $date
     * @param integer $order
     * @throws exception
     * @return array
     */
    public function getLogs($date = null, $order = 0) {
        if (empty($date)) {
            if ($this->fileOutput != true) {
                throw new \Exception('Logging is turned off in settings.');
            }
            $date = date('Y-m-d'); // if not set a specific date
        }
        $date = str_replace('-', '', $date);
        $jsonFile = 'logs/' . $date . '.json';
        if (!file_exists($jsonFile)) {
            throw new \Exception('No such file/directory.');
        }
        $content = file_get_contents($jsonFile); // read log file content
        $jsonData = json_decode($content, true); // convert content to array
        if ($order > 0) {
            return $jsonData[$order-1]; // return a specific record
        } else {
            return $jsonData; // return whole records
        }
    }

    /**
     * Send server monitoring results (detail)
     * 
     * @return array
     */
    public function list() {
        return $this->serverStatus; // send whole records with all monitoring details
    }

    /**
     * Send server monitoring result (summary)
     * 
     * @return array
     */
    public function result() {
        $result = [];
        foreach ($this->serverStatus as $status) {
            $result[$status['ip']] = $status['status'];
        }
        return $result; // send whole records with summarized info (ip and status)
    }

}

?>