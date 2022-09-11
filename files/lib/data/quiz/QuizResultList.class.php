<?php
namespace wcf\data\quiz;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of Quiz results.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizResultList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = QuizResult::class;
	
	/**
	 * Returns statistics.
	 */
	public function getStats($userID) {
		if ($userID) {
			$sql = "SELECT	COUNT(*) AS count, SUM(result) AS results
					FROM	wcf".WCF_N."_quiz_result
					WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$userID]);
			$row = $statement->fetchArray();
			if ($row['count']) {
				$row['results'] = $row['results'] / $row['count'];
			}
			else {
				$row['results']= 0;
			}
		}
		return $row;
	}
}
