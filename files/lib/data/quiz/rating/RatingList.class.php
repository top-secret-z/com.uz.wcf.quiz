<?php
namespace wcf\data\quiz\rating;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of Quiz Ratings
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class RatingList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Rating::class;
}
