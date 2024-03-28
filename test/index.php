<?php

require_once ('SunMonitor.php'); // Call 'SunMonitor' class

// Example for one server (logging off)
$status = new SunMonitor('192.168.1.1', 'Sample Server', false); // You can use any server name (for statistics), third parameter is logging setting (true/false)

// Example for many servers (mixed with ip addresses and domains)
// $status = new SunMonitor(['192.168.1.1', 'domain.com', '192.168.1.2', 'https://www.domain.com', 'https://domain.com'], ['Sample Server 1', 'Sample Server 2'], true); // logging on

// Example for Monitoring Server(s) with Detailed Results
// var_dump($status->monitor()->list()); // returns an array, don't forget to parse it

// Example for Monitoring Server(s) with Summarized Results
// var_dump($status->monitor()->result());  // returns an array, don't forget to parse it

// Example for Getting Log Records (reads from today's log file)
// print_r($status->getLogs());

// Example for Getting Log Records (reads from a specific date's log file)
// print_r($status->getLogs('2024-03-28'));

// Example for Getting Log Records (reads from a specific date's log file, shows 3rd monitoring)
// print_r($status->getLogs('2024-03-28', 3)); // first parameter is date, second parameter is order number of monitoring

?>