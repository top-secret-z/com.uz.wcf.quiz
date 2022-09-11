<?php
namespace wcf\data\quiz;
use wcf\data\DatabaseObject;

/**
 * Represents a Quiz result.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizResult extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'quiz_result';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'resultID';
}
