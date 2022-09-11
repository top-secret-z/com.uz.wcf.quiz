<?php
namespace wcf\system\page\handler;
use wcf\data\quiz\QuizList;
use wcf\data\quiz\QuizResultList;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Page handler for Quiz.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizPageHandler extends AbstractMenuPageHandler {
	/**
	 * number of unread quizzes
	 */
	protected static $unreadQuizzes;
	
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		if (self::$unreadQuizzes === null) {
			self::$unreadQuizzes = 0;
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('unplayedQuizzes');
				
				// cache does not exist or is outdated
				if ($data === null) {
					// get played quizzes
					$playedIDs = array();
					$resultList = new QuizResultList();
					$resultList->getConditionBuilder()->add('userID = ?', array(WCF::getUser()->userID));
					$resultList->readObjects();
					$playeds = $resultList->getObjects();
					foreach ($playeds as $played) {
						$playedIDs[] = $played->quizID;
					}
					$playedIDs = array_unique($playedIDs);
					
					// check mark all as read
					$objectTypeID = VisitTracker::getInstance()->getObjectTypeID('com.uz.wcf.quiz');
					$time = 0;
					$sql = "SELECT	visitTime
							FROM	wcf".WCF_N."_tracked_visit_type
							WHERE	objectTypeId = ? AND userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array($objectTypeID, WCF::getUser()->userID));
					if ($row = $statement->fetchArray()) {
						$time = $row['visitTime'];
					}
					
					// get quizzes not played
					$quizList = new QuizList();
					if (!empty($playedIDs)) {
						$quizList->getConditionBuilder()->add('quizID NOT IN (?)', array($playedIDs));
					}
					if ($time) {
						$quizList->getConditionBuilder()->add('time > ?', array($time));
					}
					$quizList->readObjects();
					$quizzes = $quizList->getObjects();
					foreach ($quizzes as $quiz) {
						if ($quiz->canPlay()) self::$unreadQuizzes ++;
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'unplayedQuizzes', self::$unreadQuizzes);
				}
				else {
					self::$unreadQuizzes = $data;
				}
			}
		}
		
		return self::$unreadQuizzes;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return WCF::getSession()->getPermission('user.quiz.canSee') ? true : false;
	}
}
