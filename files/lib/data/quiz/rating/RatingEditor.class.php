<?php
namespace wcf\data\quiz\rating;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit quiz ratings
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class RatingEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Rating::class;
}
