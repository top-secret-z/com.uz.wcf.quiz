<?php
namespace wcf\system\user\notification\object;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\quiz\question\Question;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Notification object for quiz.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Question::class;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return LinkHandler::getInstance()->getLink('QuestionList', [
				'object' => $this->getDecoratedObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return WCF::getUser()->userID;
	}
}
