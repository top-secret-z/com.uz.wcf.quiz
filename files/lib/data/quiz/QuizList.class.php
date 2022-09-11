<?php
namespace wcf\data\quiz;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of Quizes.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Quiz::class;
}
