<?php

namespace Kasha\Profiler;

/**
 * Example implementation of ProfilerReporterInterface.
 *  Implement this interface in the context of your application.
 */
class ProfilerReporter implements ProfilerReporterInterface
{
	/**
	 * @param Profiler $profiler
	 * @param string $channel
	 *
	 * @return mixed|void
	 */
	public function send($profiler, $channel = '')
	{
		$messages = $profiler->getMilestones();
		$started = $profiler->getTimeStart();
		if (count($messages) > 0) {
			// @TODO more options: db, log, email? extend!
			switch ($channel) {
				case 'dump':
					print $this->format($messages, $started);
					break;
				case 'hidden':
					print '<!--'.$this->format($messages, $started).'-->';
					break;
				case 'none':
					// fall through to default
				default:
					// do nothing
					break;
			}
		}
	}

	/**
	 * @param array $messages
	 * @param $timeStart
	 *
	 * @return mixed|string
	 */
	public function format($messages = array(), $timeStart)
	{
		$params = array(
			'totalTime' => sprintf("%0.8f", Profiler::microtimeFloat() - $timeStart),
			'maxMemoryUsage' => number_format(memory_get_peak_usage()),
			'messages' => $messages,
		);

		return print_r($params, 1);
	}
}
