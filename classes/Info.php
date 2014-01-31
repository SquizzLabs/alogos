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

class Info
{
		private static $maxGroupNameSize = 25;

		/**
		 * Retrieve the system id of a solar system.
		 *
		 * @static
		 * @param	$systemName
		 * @return int The solarSystemID
		 */
		public static function getSystemID($systemName)
		{
				return Db::queryField("select solarSystemID from ccp_systems where solarSystemName = :name", "solarSystemID",
								array(":name" => $systemName), 3600);
		}

		/**
		 * @static
		 * @param	$systemID
		 * @return array Returns an array containing the solarSystemName and security of a solarSystemID
		 */
		public static function getSystemInfo($systemID)
		{
				return Db::queryRow("select solarSystemName, security, sunTypeID from ccp_systems where solarSystemID = :systemID", array(":systemID" => $systemID), 3600);
		}

		public static function getWormholeSystemInfo($systemID) {
				if ($systemID < 3100000) return;
				return Db::queryRow("select * from ccp_zwormhole_info where solarSystemID = :systemID", array(":systemID" => $systemID), 3600);
		}

		/**
		 * @static
		 * @param	$systemID
		 * @return string The system name of a solarSystemID
		 */
		public static function getSystemName($systemID)
		{
				$systemInfo = Info::getSystemInfo($systemID);
				return $systemInfo['solarSystemName'];
		}

		/**
		 * @static
		 * @param	int $systemID
		 * @return double The system secruity of a solarSystemID
		 */
		public static function getSystemSecurity($systemID)
		{
				$systemInfo = Info::getSystemInfo($systemID);
				return $systemInfo['security'];
		}

		/**
		 * @static
		 * @param	$typeID
		 * @return string The item name.
		 */
		public static function getItemName($typeID)
		{
				$name = Db::queryField("select typeName from ccp_invTypes where typeID = :typeID", "typeName", array(":typeID" => $typeID), 3600);
				if ($name === null) {
						if ($typeID >= 500000) return "TypeID $typeID"; //throw new Exception("hey now");
						Db::execute("insert ignore into ccp_invTypes (typeID, typeName) values (:typeID, :typeName)", array(":typeID" => $typeID, ":typeName" => "TypeID $typeID"));
						$name = "TypeID $typeID";
				}
				return $name;
		}

		/**
		 * @param	$itemName
		 * @return int The typeID of an item.
		 */
		public static function getItemID($itemName)
		{
				return Db::queryField("select typeID from ccp_invTypes where upper(typeName) = :typeName", "typeID", array(":typeName" => strtoupper($itemName)), 3600);
		}

		/**
		 * Retrieves the effectID of an item.	This is useful for determining if an item is fitted into a low,
		 * medium, high, rig, or t3 slot.
		 *
		 * @param	$typeID
		 * @return int The effectID of an item.
		 */
		public static function getEffectID($typeID)
		{
				return Db::queryField("select effectID from ccp_dgmTypeEffects where typeID = :typeID and effectID in (11, 12, 13, 2663, 3772)", "effectID", array(":typeID" => $typeID), 3600);
		}

		public static function getCorpId($name)
		{
				return Db::queryField("select corporationID from skq_corporations where name = :name order by memberCount desc limit 1", "corporationID", array(":name" => $name), 3600);
		}

		public static function getAlliName($id)
		{
				return Db::queryField("select allianceName from skq_alliances where allianceID = :id limit 1", "allianceName", array(":id" => $id), 3600);
		}

		public static function getFactionId($name)
		{
				return Db::queryField("select factionID from skq_factions where name = :name", "factionID", array(":name" => $name), 3600);
		}

		public static function getFactionName($id)
		{
				return Db::queryField("select name from skq_factions where factionID = :id", "name", array(":id" => $id), 3600);
		}

		public static function getRegionName($id)
		{
				$data = Db::queryField("select regionName from ccp_regions where regionID = :id", "regionName", array(":id" => $id), 3600);
				return $data;
		}

		public static function getRegionID($name)
		{
				return Db::queryField("select regionID from ccp_regions where regionName = :name", "regionID", array(":name" => $name), 3600);
		}

		public static function getRegionIDFromSystemID($systemID)
		{
				$regionID = Db::queryField("select regionID from ccp_systems where solarSystemID = :systemID", "regionID", array(":systemID" => $systemID), 3600);
				return $regionID;
		}

		public static function getRegionInfoFromSystemID($systemID)
		{
				$regionID = Db::queryField("select regionID from ccp_systems where solarSystemID = :systemID", "regionID", array(":systemID" => $systemID), 3600);
				return Db::queryRow("select * from ccp_regions where regionID = :regionID", array(":regionID" => $regionID), 3600);
		}

		public static function getShipId($name)
		{
				$shipID = Db::queryField("select typeID from ccp_invTypes where typeName = :name", "typeID", array(":name" => $name), 3600);
				return $shipID;
		}

		/**
		 * Attempt to find the name of a corporation in the corporations table.	If not found the
		 * and $fetchIfNotFound is true, it will then attempt to pull the name via an API lookup.
		 *
		 * @static
		 * @param	$id
		 * @param bool $fetchIfNotFound
		 * @return string The name of the corp if found, null otherwise.
		 */
		public static function getCorpName($id, $fetchIfNotFound = true)
		{
				$name = Db::queryField("select corporationName from skq_corporations where corporationID = :id", "corporationName", array(":id" => $id), 3600);
				if ($name != null || $fetchIfNotFound == false) return $name;

				$pheal = Util::getPheal();
				$pheal->scope = "corp";
				$corpInfo = $pheal->CorporationSheet(array("corporationID" => $id));
				$name = $corpInfo->corporationName;
				if ($name != null) { // addName($id, $name, 1, 2, 2);
						Db::execute("insert ignore into skq_corporations (corporationID, name) values (:id, :name)", array(":id" => $id, ":name" => $name));
				}
				return $name;
		}

		public static function getAlliId($name)
		{
				return Db::queryField("select allianceID from skq_alliances where name = :name", "allianceID", array(":name" => $name), 3600);
		}

		public static function getCharId($name)
		{
				return Db::queryField("select characterID from skq_characters where name = :name", "characterID", array(":name" => $name), 3600);
		}

		/**
		 * Attempt to find the name of a character in the characters table.	If not found the
		 * and $fetchIfNotFound is true, it will then attempt to pull the name via an API lookup.
		 *
		 * @static
		 * @param	$id
		 * @param bool $fetchIfNotFound
		 * @return string The name of the corp if found, null otherwise.
		 */
		public static function getCharName($id, $fetchIfNotFound = false)
		{
				$name = Db::queryField("select characterName from skq_character_info where characterID = :id", "characterName", array(":id" => $id), 3600);
				if ($name != null || $fetchIfNotFound == false) return $name;

				$pheal = Util::getPheal();
				$pheal->scope = "eve";
				$charInfo = $pheal->CharacterInfo(array("characterid" => $id));
				$name = $charInfo->characterName;
				if ($name != null) { //addName($id, $name, 1, 1, null);
						Db::execute("insert ignore into skq_characters (characterID, name) values (:id, :name)", array(":id" => $id, ":name" => $name));
				}
				return $name;
		}

		public static function getGroupID($id)
		{
				$groupID = Db::queryField("select groupID from ccp_invTypes where typeID = :id", "groupID", array(":id" => $id), 3600);
				if ($groupID === null) return 0;
				return $groupID;
		}

		public static function getGroupIdFromName($id)
		{
				$groupID = Db::queryField("select groupID from ccp_invGroups where groupName = :id", "groupID", array(":id" => $id), 3600);
				if ($groupID === null) return 0;
				return $groupID;
		}

		/**
		 * Get the name of the group
		 *
		 * @static
		 * @param int $groupID
		 * @return string
		 */
		public static function getGroupName($groupID)
		{
				$name = Db::queryField("select groupName from ccp_invGroups where groupID = :id", "groupName", array(":id" => $groupID), 3600);
				return $name;
		}

		private static function findEntitySearch(&$resultArray, $type, $query, $search)
		{
				$results = Db::query("${query}", array(":search" => $search), 30);
				Info::addResults($resultArray, $type, $results);
		}

		private static function addResults(&$resultArray, $type, $results)
		{
				foreach ($results as $result) {
						$keys = array_keys($result);
						$result["type"] = $type;
						$value = $result[$keys[0]];
						$resultArray["$type|$value"] = $result;
				}
		}

		private static $entities = array(
						array("faction", "SELECT factionID FROM skq_factions WHERE name "),
						array("alliance", "SELECT allianceID FROM skq_alliances WHERE name "),
						array("alliance", "SELECT allianceID FROM skq_alliances WHERE ticker "),
						array("corporation", "SELECT corporationID FROM skq_corporations WHERE name "),
						array("corporation", "SELECT corporationID FROM skq_corporations WHERE ticker "),
						array("character", "SELECT characterID FROM skq_characters WHERE name "),
						array("item", "select typeID from ccp_invTypes where published = 1 and typeName "),
						array("system", "select solarSystemID from ccp_systems where solarSystemName "),
						array("region", "select regionID from ccp_regions where regionName "),
						);

		/**
		 * Search for an entity
		 *
		 * @static
		 * @param string $search
		 * @return string
		 */
		public static function findEntity($search)
		{
				$search = trim($search);
				if (!isset($search)) return "";

				$names = array();
				for ($i = 0; $i <= 1; $i++) {
						$match = $i == 0 ? " = " : " like ";
						foreach (Info::$entities as $entity) {
								$type = $entity[0];
								$query = $entity[1];
								Info::findEntitySearch($names, $type, "$query $match :search limit 9", $search . ($i == 0 ? "" : "%"));
						}
				}
				$retValue = array();
				foreach ($names as $id => $value) $retValue[] = $value;
				Info::addInfo($retValue);
				return $retValue;
		}

		public static function getPilotDetails($id)
		{
				$data = Db::queryRow("select characterID, corporationID, allianceID, factionID from skq_participants where characterID = :id order by killID desc limit 1", array(":id" => $id), 3600);
				if (count($data) == 0) {
						$data["characterID"] = $id;
						$data["characterName"] = Info::getCharName($id, true);
				}
				Info::addInfo($data);
				return Summary::getPilotSummary($data, $id);
		}

		public static function getCorpDetails($id)
		{
				$data = Db::queryRow("select corporationID, allianceID, factionID from skq_participants where corporationID = :id order by killID desc limit 1", array(":id" => $id), 3600);
				if ($data == null || count($data) == 0) $data = Db::queryRow("select corporationID, allianceID, 0 factionID from skq_corporations where corporationID = :id", array(":id" => $id), 3600);
				if ($data == null || count($data) == 0) $data["corporationID"] == $id;
				$moreData = Db::queryRow("select * from skq_corporations where corporationID = :id", array(":id" => $id), 3600);
				if ($moreData) {
						$data["memberCount"] = $moreData["memberCount"];
						$data["cticker"] = $moreData["ticker"];
						$data["ceoID"] = $moreData["ceoID"];
				}
				Info::addInfo($data);
				return Summary::getCorpSummary($data, $id);
		}

		public static function getAlliDetails($id)
		{
				$data = Db::queryRow("select allianceID, factionID from skq_participants where allianceID = :id order by killID desc limit 1", array(":id" => $id), 3600);
				if (count($data) == 0) $data["allianceID"] == $id;
				// Add membercount, etc.
				$moreData = Db::queryRow("select * from skq_alliances where allianceID = :id", array(":id" => $id), 3600);
				if ($moreData) {
						$data["memberCount"] = $moreData["memberCount"];
						$data["aticker"] = $moreData["ticker"];
						$data["executorCorpID"] = $moreData["executorCorpID"];
				}
				Info::addInfo($data);
				return Summary::getAlliSummary($data, $id);
		}

		public static function getFactionDetails($id)
		{
				$data = Db::queryRow("select factionID from skq_participants where factionID = :id order by killID desc limit 1", array(":id" => $id), 3600);
				if (count($data) == 0) $data["factionID"] = $id;
				Info::addInfo($data);
				return Summary::getFactionSummary($data, $id);
		}

		public static function getSystemDetails($id)
		{
				$data = array("solarSystemID" => $id);
				Info::addInfo($data);
				return Summary::getSystemSummary($data, $id);
		}

		public static function getRegionDetails($id)
		{
				$data = array("regionID" => $id);
				Info::addInfo($data);
				return Summary::getRegionSummary($data, $id);
		}

		public static function getGroupDetails($id)
		{
				$data = array("groupID" => $id);
				Info::addInfo($data);
				return Summary::getGroupSummary($data, $id);
		}

		public static function getShipDetails($id)
		{
				$data = array("shipTypeID" => $id);
				Info::addInfo($data);
				$data["shipTypeName"] = $data["shipName"];
				return Summary::getShipSummary($data, $id);
		}


		public static function getSystemsInRegion($id)
		{
				$result = Db::query("select solarSystemID from ccp_systems where regionID = :id", array(":id" => $id), 3600);
				$data = array();
				foreach ($result as $row) $data[] = $row["solarSystemID"];
				return $data;
		}

		public static function addInfo(&$element)
		{
				if ($element == null) return;
				foreach ($element as $key => $value) {
						if (is_array($value)) $element[$key] = Info::addInfo($value);
						else if ($value != 0) switch ($key) {
								case "trainingEndTime":
									$unixTime = strtotime($value);
									$diff = $unixTime - time();
									$element["trainingSeconds"] = $diff;
								break;
								case "lastChecked":
										//$element["lastCheckedTime"] = date("Y-m-d H:i", $value);
								break;
								case "cachedUntil":
								case "queueFinishes":
								case "endTime":
									$unixTime = strtotime($value);
									$diff = $unixTime - time();
									$element["${key}Seconds"] = $diff;
								break;
								case "unix_timestamp":
										$element["ISO8601"] = date("c", $value);
								$element["killTime"] = date("Y-m-d H:i", $value);
								$element["MonthDayYear"] = date("F j, Y", $value);
								break;
								case "shipTypeID":
										if (!isset($element["shipName"])) $element["shipName"] = Info::getItemName($value);
								if (!isset($element["groupID"])) $element["groupID"] = Info::getGroupID($value);
								if (!isset($element["groupName"])) $element["groupName"] = Info::getGroupName($element["groupID"]);
								break;
								case "groupID":
										global $loadGroupShips; // ugh
								if (!isset($element["groupName"])) $element["groupName"] = Info::getGroupName($value);
								if ($loadGroupShips && !isset($element["groupShips"]) && !isset($element["noRecursion"])) $element["groupShips"] = Db::query("select typeID as shipTypeID, typeName as shipName, raceID, 1 as noRecursion from ccp_invTypes where groupID = :id and published = 1 and marketGroupID is not null order by raceID, marketGroupID, typeName", array(":id" => $value), 3600);
								break;
								case "executorCorpID":
										//$element["executorCorpName"] = Info::getCorpName($value);
								break;
								case "ceoID":
										$element["ceoName"] = Info::getCharName($value);
								break;
								case "characterID":
										$element["characterName"] = Info::getCharName($value);
								break;
								case "corporationID":
										$element["corporationName"] = Info::getCorpName($value);
								break;
								case "allianceID":
										$element["allianceName"] = Info::getAlliName($value);
								break;
								case "factionID":
										$element["factionName"] = Info::getFactionName($value);
								break;
								case "weaponTypeID":
										$element["weaponTypeName"] = Info::getItemName($value);
								break;
								case "typeID":
										if (!isset($element["typeName"])) $element["typeName"] = Info::getItemName($value);
								$groupID = Info::getGroupID($value);
								if (!isset($element["groupID"])) $element["groupID"] = $groupID;
								if (!isset($element["groupName"])) $element["groupName"] = Info::getGroupName($groupID);
								//if (!isset($element["fittable"])) $element["fittable"] = Info::getEffectID($value) != null;
								break;
								case "level":
								case "trainingToLevel":
									$tLevels = array("I", "II", "III", "IV", "V");
									$element["tLevel"] = $tLevels[$value - 1];
								break;
						}
				}
				return $element;
		}
}
