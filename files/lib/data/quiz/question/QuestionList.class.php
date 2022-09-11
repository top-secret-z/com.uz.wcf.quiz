<?php
namespace wcf\data\quiz\question;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of Questions.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuestionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Question::class;
}
