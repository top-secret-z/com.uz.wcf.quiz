<?php
namespace wcf\system\user\notification\event;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * User notification event for quiz.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('wcf.user.quiz.notification.question.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('wcf.user.quiz.notification.question.message', [
				'author' => $this->author,
				'question' => $this->userNotificationObject
	]);
	}
	
	public function getEmailMessage($notificationType = 'instant') {
		return [
				'message-id' => 'com.uz.wcf.quiz.question/'.$this->getUserNotificationObject()->questionID,
				'template' => 'email_notification_quizQuestion',
				'application' => 'wcf',
				'variables' => [
						'languageVariablePrefix' => 'wcf.user.quiz.notification.question'
				]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('QuizQuestionList',
			[
					'object' => $this->userNotificationObject,
					'questionID' => $this->userNotificationObject->questionID,
					'forceFrontend' => true
			],
			'#question'.$this->userNotificationObject->questionID
		);
	}
}
