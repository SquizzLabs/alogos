<?php
/* Alliance Logos
 * Copyright (C) 2013 SquizzLabs
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Util
{
	public static function getPheal($keyID = null, $vCode = null)
	{
		$base = dirname(__FILE__);
		$base .= "/../util/";
		require_once "$base/pheal/config.php";
		$pheal = new Pheal($keyID, $vCode);
		return $pheal;
	}

	public static function pluralize($string)
	{
		if (!Util::endsWith($string, "s")) return $string . "s";
		else return $string . "es";
	}

	public static function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	public static function endsWith($haystack, $needle)
	{
		return substr($haystack, -strlen($needle)) === $needle;
	}

	public static function firstUpper($str)
	{
		if (strlen($str) == 1) return strtoupper($str);
		$str = strtolower($str);
		return strtoupper(substr($str, 0, 1)) . substr($str, 1);
	}

	private static $formatIskIndexes = array("", "k", "m", "b", "t", "tt", "ttt");

	public static function formatIsk($value)
	{
		$numDecimals = (((int)$value) == $value) && $value < 10000 ? 0 : 2;
		if ($value == 0) return number_format(0, $numDecimals);
		if ($value < 10000) return number_format($value, $numDecimals);
		$iskIndex = 0;
		while ($value > 999.99) {
			$value /= 1000;
			$iskIndex++;
		}
		return number_format($value, $numDecimals) . self::$formatIskIndexes[$iskIndex];
	}

	public static function shortString($string, $maxLength)
	{
		if (strlen($string) <= $maxLength) return $string;
		return substr($string, 0, $maxLength - 3) . "...";
	}

	public static function pageTimer()
	{
		global $timer;
		return $timer->stop();
	}

	public static function isActive($pageType, $currentPage, $retValue = "active")
	{
		return strtolower($pageType) == strtolower($currentPage) ? $retValue : "";
	}

	private static $months = array("", "JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC");

	public static function getMonth($month)
	{
		return self::$months[$month];
	}

	private static $longMonths = array("", "January", "February", "March", "April", "May", "June", "July", "August",
																		 "September", "October", "November", "December");

	public static function getLongMonth($month)
	{
		return self::$longMonths[$month];
	}
}
