<?php
namespace wcf\data\quiz\question;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit Questions.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuestionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Question::class;
}
