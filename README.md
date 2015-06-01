# kasha-profiler

This library is meant to provide profiling functionality for the applications built on top of the Kasha framework.
However, it does not require any of Kasha libraries or modules to work, so anyone who likes the simplicity of kasha-profiler, is welcome to use it.

The library provides only the most essential parts of profiling functionality - measuring the operations time.
Usage of its main class, Profiler, can be described in the shortest way by this simple list:

 - start a new timer whenever you have an operation you want to measure
 - stop the timer by its ID (or stop all the timers of given type)
 - get the statistics of measured operations and optionally, use some reporter class to process this stats

Here is a dead-simple example of using the Kasha Profiler:

```php
<?php

require 'vendor/autoload.php';

use Kasha\Profiler\Profiler;

Profiler::getInstance()->addMilestone('script started');

for ($i = 0; $i < 3; $i++) {
    $sleepTimer = Profiler::startTimer('sleep');
    usleep(rand(3000, 80000)); // sleep randomly between 3ms and 80ms
    Profiler::stopTimer($sleepTimer, 'timer #' . $sleepTimer . ' stopped');
}

print_r(Profiler::getInstance()->getTimers());
```

If there is no activity to measure, but some important milestone should be logged, then just a "message" is added.
Milestone is by default measured in relation with the global start timestamp for the profiler (which is set up in constructor method of Profiler object), but it is possible to provide a different timestamp if required.

Most obvious methods are provided as static methods, which can be used throughout of PHP application thanks to the singleton nature of Profiler class.

For less common methods, there are some non-static public methods which can still be executed on the instance that is accessed with static getInstance() method.

## Installation

Install FSCMS library with Composer by adding a requirement into composer.json of your project:

```json
{
   "require": {
        "barcodex/kasha-profiler": "*"
   }
}
```

or requiring it from the command line:

```bash
composer require barcodex/kasha-profiler:*
```

## Profiler

Kasha\Profiler class provides the foundation for measuring the times using timers.

Each timer is just an array that stores information about start/stop timestamps, message and type of the timer.
Type field can be used for grouping similar timers together (for example, for measuring the performance of database queries).
Every timer is identified by the id which is a running sequence during the lifetime of Profiler object.
Timers never get deleted, so the last id always matches the total number of timers.

The following table is the list of static methods of Profiler class that are available in any place of your application:

|method | description|
|startTimer($type) | Starts a new timer, optionally giving it a type|
|stopTimer($id) | Stops a timer with given $id, providing a $message|
|stopTypedTimers($type, $message) | Stops all timers of given $type, providing the same timestamp and $message|

And here are non-static methods, which are used less frequently:

|method | description|
|addMilestone($text) | Saves a milestone with the descriptive text|
|getMilestones() | Lists all saved milestones|
|getTimers()|Lists all saved timers (that reach the threshold value)|
|getTypedTimers($type) | Lists all saved timers (that reach the threshold value) by given $type|
|getTimerTypes() | Lists IDs of all saved timers aggregated by timer types|
|setProfilerThreshold($profilerThreshold) | Sets a threshold for timer duration|
|getProfilerThreshold() | Gets current threshold value for timer duration|

There are some methods that can be used on the instance of Profiler class (and which are actually wrapped by static methods):

|method | description|
|createTimer($type) | Starts a new timer, optionally giving it a type|
|finalizeTimer($id, $message) | Stops a timer with given $id, providing a $message|
|finalizeTypedTimers($type, $message) | Stops all timers of given $type, providing the same timestamp and $message|

## ProfilerReporterInterface and ProfilerReporter

All timers are stored inside of Profiler object, which does not take care of serializing them anywhere.

For basic functionality, we provide a simple Kasha\ProfilerReporter class.
Here is a small example for using ProfilerReporter:

```php
<?php

require 'vendor/autoload.php';

use Kasha\Profiler\Profiler;

$profiler = Profiler::getInstance();
...
$profilerReporter = new ProfilerReporter();
$profilerReporter->send('dump');
```

Since Profiler is a singleton, there is no need to connect it to ProfilerReporter, the latter can always get its instance.

