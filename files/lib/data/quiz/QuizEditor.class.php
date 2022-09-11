<?php
namespace wcf\data\quiz;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit Quizes.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Quiz::class;
}
