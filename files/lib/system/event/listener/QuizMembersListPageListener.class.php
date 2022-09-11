<?php
namespace wcf\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds quizzs sort fields for members list.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizMembersListPageListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (QUIZ_USER_QUIZZES) $eventObj->validSortFields[] = 'uzQuiz';
		if (QUIZ_USER_QUIZRATES) $eventObj->validSortFields[] = 'uzQuizRate';
	}
}
