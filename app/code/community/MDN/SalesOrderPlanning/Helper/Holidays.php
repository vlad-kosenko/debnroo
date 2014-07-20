<?php

class MDN_SalesOrderPlanning_Helper_Holidays extends Mage_Core_Helper_Abstract
{
	/**
	 * return true if an day is holy day
	 *
	 * @param unknown_type $date
	 */
	public function isHolyDay($dateTimestamp)
	{
		
		//check weekend
		$dayId = date('w', $dateTimestamp);
		$weekendDay = Mage::getStoreConfig('general/locale/weekend');
		$pos = strpos($weekendDay, $dayId);
		if (!($pos === false))
		{
			return true;
		}

			
		$day = date('d', $dateTimestamp);
		$month = date('m', $dateTimestamp);
		$year = date('Y', $dateTimestamp);
		$country = mage::getStoreConfig('general/country/default');
		switch ($country)
		{
			case 'FR':
				//todo: add holydays logic
				$dayMonth = $day.'-'.$month;
				switch ($dayMonth)
				{
					case '01-01':
					case '01-05':
					case '08-05':
					case '14-07':
					case '15-08':
					case '01-11':
					case '11-11':
					case '25-12':
						return true;
						break;
				}
				
				break;
			default:
				//todo: add holydays logic
								
				break;
		}
		
		return false;
	}
	
	/**
	 * Return next day that is not holy day
	 *
	 * @param unknown_type $dateTimestamp
	 * @return unknown
	 */
	public function getNextDayThatIsNotHolyday($dateTimestamp)
	{
		$dateTimestamp += 3600 * 24;
		while($this->isHolyDay($dateTimestamp))
		{
			$dateTimestamp += 3600 * 24;			
		}
		return $dateTimestamp;
	}
	
	/**
	 * Return date + days avoiding holy days
	 * Caution : return timestamp
	 *
	 * @param unknown_type $fromDate
	 * @param unknown_type $dayCount
	 */
	public function addDaysWithoutHolyDays($fromDateTimestamp, $dayCount)
	{
		for($i=1;$i<=$dayCount;$i++)
		{
			$fromDateTimestamp = $this->getNextDayThatIsNotHolyday($fromDateTimestamp);
		}
		return $fromDateTimestamp;
	}
}