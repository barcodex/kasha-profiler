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
	public $messages = array();

	/** @var array */
	public $timers = array();

	/** @var array */
	public $timerTypes = array();

	/** @var array */
	public $timersStarted = array();

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

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new Profiler();
		}

		return self::$instance;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function getTimers()
	{
		return $this->timers;
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

	public function finalizeTimer($id, $message)
	{
		$output = false;
		if (isset($this->timers[$id])) {
			$this->timers[$id]['stopped'] = self::microtimeFloat();
			$this->timers[$id]['duration'] = $this->timers[$id]['stopped'] - $this->timers[$id]['started'];
			$this->timers[$id]['message'] = $message;
			$output = $this->timers[$id];
		}

		return $output;
	}

	/**
	 * Finalize all timers of given type with the same timestamp
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
				$this->timers[$id]['stopped'] = $stoppedTimestamp;
				$this->timers[$id]['duration'] = $stoppedTimestamp - $this->timers[$id]['started'];
				$this->timers[$id]['message'] = $message;
				$output[] = $this->timers[$id];
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
	 * Registers timestamp with microsecond precision and descriptive text
	 *
	 * @param string $text
	 * @param float $activityStarted
	 */
	public function addMessage($text, $activityStarted = null)
	{
		$activityEnded = self::microtimeFloat();
		$totalDuration = $activityEnded - $this->timeStart;
		$activityDuration = null;
		if ($activityStarted !== null) {
			$activityDuration = $activityEnded - $activityStarted;
			$text .= sprintf(" in %0.4f milliseconds", $activityDuration * 1000);
		}
		if ($activityStarted === null || $activityDuration > $this->profilerThreshold) {
			$this->messages[] = array('time' => sprintf("%0.4f", $totalDuration), 'text' => $text);
		}
	}

	public function sendReport($channel = '')
	{
		if (!is_null($this->reporter)) {
			$this->reporter->send($this, $channel);
		}
	}

}
