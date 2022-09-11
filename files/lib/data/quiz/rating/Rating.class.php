<?php
namespace wcf\data\quiz\rating;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a quiz rating
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class Rating extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'quiz_rating';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'ratingID';
	
	/**
	 * Returns the actual user's rating for a specific quiz.
	 */
	public static function getRating($quizID) {
		$rating = 0;
		$sql = "SELECT	rating
				FROM	wcf".WCF_N."_quiz_rating
				WHERE	quizID = ? AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array($quizID, WCF::getUser()->userID));
		if ($row = $statement->fetchArray()) {
			$rating = $row['rating'];
		}
		return $rating;
	}
}
