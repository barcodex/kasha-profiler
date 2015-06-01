<?php

namespace Kasha\Profiler;

class Profiler
{
	/** @var null Profiler */
	private static $instance = null;

	/** @var float */
	private $profilerThreshold = 0.0;

	/** @var float */
	private $timeStart;

	/** @var array */
	public $milestones = array();

	/** @var array */
	public $timers = array();

	/** @var array */
	public $timerTypes = array();

	/** @var ProfilerReporterInterface */
	private $reporter = null;

	public function __construct()
	{
		$this->timeStart = self::microtimeFloat();
	}

	/**
	 * @return float
	 */
	public function getTimeStart()
	{
		return $this->timeStart;
	}

	/**
	 * @return float
	 */
	public function getProfilerThreshold()
	{
		return $this->profilerThreshold;
	}

	/**
	 * @param float $profilerThreshold
	 */
	public function setProfilerThreshold($profilerThreshold)
	{
		$this->profilerThreshold = 1.0 * $profilerThreshold;
	}

	/**
	 * @param $reporter ProfilerReporterInterface
	 */
	public function setProfilerReporter($reporter)
	{
		$this->reporter = $reporter;
	}

    /**
     * Profiler class is a singleton.
     *  This method returns the instance for calling non-static functions when required.
     * @return Profiler|null
     */
    public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new Profiler();
		}

		return self::$instance;
	}

	public function getMilestones()
	{
		return $this->milestones;
	}

    /**
     * Lists all saved timers (that reach the threshold value)
     *
     * @return array
     */
    public function getTimers()
	{
        $output = array();

        foreach ($this->timers as $timer) {
            if (isset($timer['stopped']) && isset($timer['duration'])) {
                if ($this->profilerThreshold == 0.0 || $timer['duration'] > $this->profilerThreshold) {
                    $output[] = $timer;
                }
            }
        }

		return $output;
	}

    /**
     * Lists all saved timers (that reach the threshold value) by given $type
     *
     * @param string $type
     * @return array
     */
    public function getTypedTimers($type = '')
    {
        $output = array();

        $types = ($type == '') ? $this->timerTypes : array($type); // if $type not provided, return all known types
        foreach ($types as $type) {
            foreach ($this->timerTypes[$type] as $timerId) {
                $timer = $this->timers[$timerId];
                if (isset($timer['stopped']) && isset($timer['duration'])) {
                    if ($this->profilerThreshold == 0.0 || $timer['duration'] > $this->profilerThreshold) {
                        $output[$type][] = $timer;
                    }
                }
            }
        }

        return $output;
    }

	/**
	 * Return timestamp with microsecond precision (PHP 4 compatible)
	 *
	 * @return float
	 */
	public static function microtimeFloat()
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float) $usec + (float) $sec);
	}

	public function createTimer($type = '')
	{
		$cnt = count($this->timers);
		$this->timers[$cnt] = array('started' => self::microtimeFloat());
		$this->timerTypes[$type][] = $cnt;

		return $cnt + 1;
	}

    /**
     * Finalizes the timer and returns its data (or false if such timer is not set)
     *
     * @param $id
     * @param $message
     * @return bool
     */
    public function finalizeTimer($id, $message)
	{
		$output = false;
		if (isset($this->timers[$id])) {
            $output = $this->finalizeTimerById($id, self::microtimeFloat(), $message);
		}

		return $output;
	}

	/**
	 * Finalize all timers of given type with the same timestamp and message
	 *
	 * @param string $type
	 * @param string $message
	 *
	 * @return bool
	 */
	public function finalizeTypedTimers($type, $message)
	{
		$output = false;
		if (isset($this->timerTypes[$type])) {
			$stoppedTimestamp = self::microtimeFloat();
			foreach($this->timerTypes[$type] as $id) {
                $output[] = $this->finalizeTimerById($id, $stoppedTimestamp, $message);
			}
		}

		return $output;
	}

	/**
	 * Create a new timer of given type and return its id (used later for stopping)
	 *
	 * @param string $type
	 *
	 * @return int
	 */
	public static function startTimer($type = '')
	{
		return self::getInstance()->createTimer();
	}

	/**
	 * Stop timer identified with $id, store the message and duration, and return the resulting array
	 *
	 * @param $id
	 * @param $message
	 *
	 * @return bool
	 */
	public static function stopTimer($id, $message)
	{
		return self::getInstance()->finalizeTimer($id, $message);
	}

	/**
	 * Stop all timers of given type
	 *
	 * @param $type
	 * @param $message
	 *
	 * @return bool
	 */
	public static function stopTypedTimers($type, $message)
	{
		return self::getInstance()->finalizeTypedTimers($type, $message);
	}

	/**
	 * Registers milestone timestamp with microsecond precision and descriptive text
	 *
	 * @param string $text
	 */
	public function addMilestone($text)
	{
		$milestoneTimestamp = self::microtimeFloat();
		$milestoneOffset = $milestoneTimestamp - $this->timeStart;
        $this->milestones[] = array('time' => sprintf("%0.4f", $milestoneOffset), 'text' => $text);
	}

	public function sendReport($channel = '')
	{
		if (!is_null($this->reporter)) {
			$this->reporter->send($this, $channel);
		}
	}

    /**
     * Finalizes the timer by setting its "stopped", "duration" and "message" parameters
     *
     * @param $id
     * @param $timestamp
     * @param $message
     * @return mixed
     */
    private function finalizeTimerById($id, $timestamp, $message)
    {
        $this->timers[$id]['stopped'] = self::microtimeFloat();
        $this->timers[$id]['duration'] = $timestamp - $this->timers[$id]['started'];
        $this->timers[$id]['message'] = $message;

        return $this->timers[$id];
    }

}
