<?php

namespace PhpPupeeteer\Traits;

trait Throttler {
	private $SECONDS_IN_MINUTE = 60;
	private $START_OF_MINUTE = 'Y-m-d H:i:00';
	private $MINIMUM_WAIT_SECONDS = 1;

	private $APM = 0;
	private $actionsByMinutes = [];
	private $calculationPeriod = '';

	private $firstActionTs = 0;

	public function enableThrottler(int $APM = 30, string $calculationPeriod = '-5 minute')
    {
	    $this->setApm($APM)->setApmPeriod($calculationPeriod);
	    return $this;
    }

    public function disableThrottler()
    {
        $this->setApm(0);
        return $this;
    }

    private function setApm(int $APM = 30)
    {
        $this->APM = $APM;
        return $this;
    }

    private function setApmPeriod(string $calculationPeriod = '-5 minute')
    {
        if ($calculationPeriod !== '') {
            $this->calculationPeriod = $calculationPeriod;
        }

        return $this;
    }

    private function isDisabled()
    {
        return $this->APM === 0;
    }

	public function addAction()
    {
        if ($this->isDisabled()) {
            return $this;
        }

        if ($this->firstActionTs === 0) {
            $this->firstActionTs = time();
        }
        $currentMinuteTs = strtotime(date($this->START_OF_MINUTE));
        if (!array_key_exists($currentMinuteTs, $this->actionsByMinutes)) {
            $this->actionsByMinutes[$currentMinuteTs] = 0;
        }
        $this->actionsByMinutes[$currentMinuteTs]++;

		return $this;
	}

	/**
	 * Ожидаем, пока APM не снизится ниже.
	 *
	 * @return $this
	 */
	public function waitForApm()
    {
        if ($this->isDisabled()){
            return $this;
        }

        do {
            sleep($this->MINIMUM_WAIT_SECONDS);
        } while($this->calcAPM() >= $this->APM);
		return $this;
	}

	private function getMinimumTs()
    {
		return strtotime(date($this->START_OF_MINUTE, strtotime($this->calculationPeriod)));
	}

	public function calcApm()
    {
        if ($this->isDisabled()){
            return 0;
        }

		$minimumMinuteTs = $this->getMinimumTs();
		$oldestActionTs = 0;
		$result = 0;

		foreach ($this->actionsByMinutes as $minuteTs => $actionsCount) {
			//Удаляем старые записи.
			if ($minimumMinuteTs > $minuteTs) {
				unset($this->actionsByMinutes[$minuteTs]);
				continue;
			}

			if ($oldestActionTs === 0) {
				$oldestActionTs = $minuteTs;
			}

			$result += $actionsCount;
		}

		//Когда данных мало APM занижается, и это позволяет получить реальные данные.
		if ($oldestActionTs < $this->firstActionTs) {
			$oldestActionTs = $this->firstActionTs;
		}

		$minutesCount = (time() - $oldestActionTs) / $this->SECONDS_IN_MINUTE;
		$minutesCount = $minutesCount ?: 1;

		$result = round($result / $minutesCount);

		return $result;
	}
}