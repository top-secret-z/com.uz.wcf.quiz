<?php
namespace wcf\data\quiz\rating;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizAction;
use wcf\system\cache\builder\QuizBestRatedBoxCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\GroupedUserList;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Executes quiz rating related actions
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class RatingAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction {
	/**
	 * @inheritDoc
	 */
	protected $className = RatingEditor::class;
	
	/**
	 * quiz object
	 */
	protected $quiz = null;
	
	/**
	 * Validates preparation of a new rating.
	 */
	public function validatePrepareRating() {
		if (!isset($this->parameters['quizID'])) {
			throw new PermissionDeniedException();
		}
		$this->quiz = new Quiz($this->parameters['quizID']);
		if (!$this->quiz->quizID) {
			throw new IllegalLinkException();
		}
		
		if (!QUIZ_RATING_ACTIVATE || !WCF::getSession()->getPermission('user.quiz.canRate')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Prepares the rating dialog for a quiz.
	 */
	public function prepareRating() {
		// must have played before rating
		$rating = Rating::getRating($this->quiz->quizID);
		
		WCF::getTPL()->assign([
				'rating' => $rating,
				'hasSolved' => $this->quiz->hasSolved()
		]);
		
		return [
				'rating' => $rating,
				'template' => WCF::getTPL()->fetch('quizRatingDialog')
		];
	}
	
	/**
	 * Validates rating of a thread.
	 */
	public function validateRate() {
		if (!isset($this->parameters['quizID'])) {
			throw new PermissionDeniedException();
		}
		$this->quiz = new Quiz($this->parameters['quizID']);
		if (!$this->quiz->quizID) {
			throw new IllegalLinkException();
		}
		
		if (!QUIZ_RATING_ACTIVATE || !WCF::getSession()->getPermission('user.quiz.canRate')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Rates a quiz.
	 */
	public function rate() {
		// already rated?
		$rating = Rating::getRating($this->quiz->quizID);
		$newRating = $this->quiz->rating;
		$newRatingCount = $this->quiz->ratingCount;
		
		// delete first, then insert new, if rated
		if ($rating) {
			$sql = "DELETE FROM	wcf".WCF_N."_quiz_rating
					WHERE		quizID = ? AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->quiz->quizID, WCF::getUser()->userID]);
			
			$newRating -= $rating;
			$newRatingCount --;
		}
		//new rating
		$objectAction = new RatingAction([], 'create', [
				'data' => [
						'quizID' => $this->quiz->quizID,
						'userID' => WCF::getUser()->userID,
						'time' => TIME_NOW,
						'rating' => $this->parameters['rating']
				]
		]);
		$objectAction->executeAction();
		
		// update quiz
		$newRating += $this->parameters['rating'];
		$newRatingCount ++;
		$objectAction = new QuizAction([$this->quiz->quizID], 'update', [
				'data' => [
						'rating' => $newRating,
						'ratingCount' => $newRatingCount,
						'ratingTotal' => $newRating / $newRatingCount
				]
		]);
		$objectAction->executeAction();
		
		if (MODULE_UZQUIZ_ACTIVITY && $rating == 0) {
			UserActivityEventHandler::getInstance()->fireEvent('com.uz.wcf.quiz.recentActivityEvent.quizRating', $this->quiz->quizID);
		}
		
		// reset cache
		QuizBestRatedBoxCacheBuilder::getInstance()->reset();
		
		// inform the dialog
		return ['rated' => 1];
	}
	
	/**
	 * Validates unrating of a quiz.
	 */
	public function validateUnrate() {
		$this->validateRate();
	}
	
	/**
	 * Unrates a quiz.
	 */
	public function unrate() {
		$rating = Rating::getRating($this->quiz->quizID);
		$newRating = $this->quiz->rating - $rating;
		$newRatingCount = $this->quiz->ratingCount - 1;
		
		// just delete 
		$sql = "DELETE FROM	wcf".WCF_N."_quiz_rating
				WHERE		quizID = ? AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quiz->quizID, WCF::getUser()->userID]);
		
		// update quiz
		$objectAction = new QuizAction([$this->quiz->quizID], 'update', [
				'data' => [
						'rating' => $newRating,
						'ratingCount' => $newRatingCount,
						'ratingTotal' => $newRatingCount ? $newRating / $newRatingCount : 0
				]
		]);
		$objectAction->executeAction();
		
		// reset cache
		QuizBestRatedBoxCacheBuilder::getInstance()->reset();
		
		// inform the dialog
		return ['rated' => -1];
	}
	
	/**
	 * Validates getGroupedUserList action.
	 */
	public function validateGetGroupedUserList() {
		if (!isset($this->parameters['data']['quizID'])) {
			throw new PermissionDeniedException();
		}
		$this->quiz = new Quiz($this->parameters['data']['quizID']);
		if (!$this->quiz->quizID) {
			throw new IllegalLinkException();
		}
		
		if (!QUIZ_RATING_ACTIVATE || !WCF::getSession()->getPermission('user.quiz.canSeeRatingDetails')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getGroupedUserList() {
		// get rating options
		$ratings = [];
		$ratings[1] = new GroupedUserList(WCF::getLanguage()->get(QUIZ_RATING_ONE), 'wcf.user.quiz.rating.noRating');
		$ratings[2] = new GroupedUserList(WCF::getLanguage()->get(QUIZ_RATING_TWO), 'wcf.user.quiz.rating.noRating');
		$ratings[3] = new GroupedUserList(WCF::getLanguage()->get(QUIZ_RATING_THREE), 'wcf.user.quiz.rating.noRating');
		$ratings[4] = new GroupedUserList(WCF::getLanguage()->get(QUIZ_RATING_FOUR), 'wcf.user.quiz.rating.noRating');
		$ratings[5] = new GroupedUserList(WCF::getLanguage()->get(QUIZ_RATING_FIVE), 'wcf.user.quiz.rating.noRating');
		
		// get ratings
		$sql = "SELECT	userID, rating
				FROM	wcf".WCF_N."_quiz_rating
				WHERE	quizID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quiz->quizID]);
		$ratingData = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($ratingData[$row['rating']])) {
				$ratingData[$row['rating']] = [];
			}
			
			$ratingData[$row['rating']][] = $row['userID'];
		}
		foreach ($ratingData as $rating => $userIDs) {
			$ratings[$rating]->addUserIDs($userIDs);
		}
		
		// load user profiles
		GroupedUserList::loadUsers();
		
		WCF::getTPL()->assign([
				'groupedUsers' => $ratings
		]);
		
		return [
				'pageCount' => 1,
				'template' => WCF::getTPL()->fetch('groupedUserList')
		];
	}
}
