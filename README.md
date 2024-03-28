# PHP Server Monitoring Class

SunMonitor is a PHP Server Monitoring class that monitors servers by using a basic ping process and stores the results in a JSON file that is separated by dates.

You can use this class to monitor your project server(s) and show results instantly (online) on a special web page to your visitors. Also, you can create a CronJob and get an email alert when your server(s) is down/unreachable after integrating the SunMail class into this class.

<hr>

### Table of Contents

- **[Initialization](#initialization)**
- **[Monitoring Servers](#monitoring-servers)**
- **[Getting Log Records](#getting-log-records)**

### Installation

To utilize this class, first import SunMonitor.php into your project, and require it.
SunMonitor requires PHP 5.5+ to work.

```php
require_once ('SunMonitor.php');
```

### Initialization

Simple initialization with one server:
```php
$status = new SunMonitor('192.168.1.1', 'Sample Server');
```

Simple initialization with many servers:
```php
$status = new SunMonitor(['192.168.1.1',
                          'domain.com',
                          '192.168.1.2',
                          'https://www.domain.com',
                          'https://domain.com'
                        ]);
```

Setting server names for statistics:
```php
$status = new SunMonitor(['192.168.1.1',
                          'domain.com',
                          '192.168.1.2',
                          'https://www.domain.com',
                          'https://domain.com'
                         ],
                         ['Sample Server 1',
                          'Sample Server 2'
                         ]
                        );
```

If you want log monitoring results:
```php
$status = new SunMonitor(['192.168.1.1',
                          'domain.com',
                          '192.168.1.2',
                          'https://www.domain.com',
                          'https://domain.com'
                         ],
                         ['Sample Server 1',
                          'Sample Server 2'
                         ],
                         true
                        );
```

`server name` and `logging` parameters are optional.

First parameter is `server definitions` (ip address and/or domain name), Second parameter is `server names` (set any you want), Third parameter is `logging monitoring results` to a json file (true/false)

### Monitoring Servers

Simple example with detailed results:
```php
$result = $status->monitor()->list(); //Returns an array. Don't forget to parse it!

foreach ($result as $key => $value) {
    echo $key . ' = ' . $value;
}
```

Simple example with summarized results:
```php
$result = $status->monitor()->result(); //Returns an array. Don't forget to parse it!

foreach ($result as $key => $value) {
    echo $key . ' = ' . $value;
}
```

The above examples start server monitoring and send results to the screen (detailed or summarized).

You can use `status` key for message customization. `Available` value means, the server is operational; `Unavailable` value means, the server is down. For example; you can use Green and Red dots/icons or related images instead of these terms.

### Getting Log Records

For getting log records without any parameters:
```php
$log = $status->getLogs(); //Returns an array. Don't forget to parse it!

var_dump($log);
```

For getting log records for a specific date:
```php
$log = $status->getLogs('2024-01-01'); //Returns an array. Don't forget to parse it!

var_dump($log);
```

For getting log records for a specific date's Xth monitoring:
```php
$log = $status->getLogs('2024-01-01', 2); //Returns an array. Don't forget to parse it!

var_dump($log);
```

`date` and `attempt` parameters are optional.

Log records are stored in a JSON file that is separated by dates. You can find these files in `logs` directory. If you don't set `logging` parameter as `true` while initialization, this feature does not work, but you can reach old records (if you stored them).

If you don't need these archive files, don't open this setting for faster processing and less memory usage.
