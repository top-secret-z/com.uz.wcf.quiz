<?php
namespace wcf\system\user\activity\event;
use wcf\data\quiz\QuizList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for quiz ratin.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizRatingUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		// fetch quizzes
		$quizList = new QuizList();
		$quizList->getConditionBuilder()->add("quiz.quizID IN (?)", [$objectIDs]);
		$quizList->readObjects();
		$quizzes = $quizList->getObjects();
		
		// set message
		foreach ($events as $event) {
			if (isset($quizzes[$event->objectID])) {
				if (!$quizzes[$event->objectID]->canSee()) {
					continue;
				}
				
				$event->setIsAccessible();
				
				// title
				$text = WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.rating.recentActivity', ['quiz' => $quizzes[$event->objectID]]);
				$event->setTitle($text);
				
				// description
				$event->setDescription('');
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
