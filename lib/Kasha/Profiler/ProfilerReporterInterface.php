<?php

namespace Kasha\Profiler;

interface ProfilerReporterInterface
{
	/**
	 * @param Profiler
	 * @param string $channel
	 *
	 * @return mixed
	 */
	public function send($profiler, $channel = '');

	/**
	 * @param array $messages
	 * @param $timeStart
	 *
	 * @return mixed
	 */
	public function format($messages = array(), $timeStart);
}
