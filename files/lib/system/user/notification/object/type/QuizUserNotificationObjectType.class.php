<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\quiz\question\Question;
use wcf\data\quiz\question\QuestionList;
use wcf\system\user\notification\object\QuizUserNotificationObject;

/**
 * Represents a quiz notification object type.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = QuizUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Question::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = QuestionList::class;
	
}