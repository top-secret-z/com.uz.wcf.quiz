<?php
namespace wcf\data\quiz;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a Quiz.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class Quiz extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'quiz';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'quizID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->title;
	}
	/**
	 * get translated title
	 */
	public function getTranslatedTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * get translated text
	 */
	public function getText() {
		return WCF::getLanguage()->get($this->text);
	}
	
	/**
	 * get time
	 */
	public function getTime() {
		return $this->time;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Quiz', [
				'quizID' => $this->quizID,
				'forceFrontend' => true
		], '#quiz'.$this->quizID);
	}
	
	/**
	 * return this quiz's questionIDs
	 */
	public function getQuestionIDs() {
		$ids = [];
		$sql = "SELECT	questionID
				FROM	wcf".WCF_N."_quiz_to_question
				WHERE	quizID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quizID]);
		while ($row = $statement->fetchArray()) {
			$ids[] = $row['questionID'];
		}
		return $ids;
	}
	
	/**
	 * return this quiz's questionIDs with correct answer
	 */
	public function getQuestionIDsCorrect() {
		$data = [];
		$sql = "SELECT	questionID, correct
				FROM	wcf".WCF_N."_quiz_question
				WHERE	questionID IN (SELECT questionID FROM wcf".WCF_N."_quiz_to_question WHERE quizID = ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quizID]);
		while ($row = $statement->fetchArray()) {
			$data[$row['questionID']] = $row['correct'];
		}
		return $data;
	}
	
	/**
	 * return this quiz's number of questions
	 */
	public function getQuestionCount() {
		$ids = [];
		$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_quiz_to_question
				WHERE	quizID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quizID]);
		return $statement->fetchColumn();
	}
	
	/**
	 * return how often the user solved this quiz
	 */
	public function getUserCount($userID) {
		$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_quiz_to_user
				WHERE	quizID = ? AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quizID, $userID]);
		return $statement->fetchColumn();
	}
	
	/**
	 * return if the user has already solved this quiz
	 */
	public function hasSolved() {
		$played = explode(',', WCF::getUser()->uzQuizPlayed);
		if (in_array($this->quizID, $played)) return true;
		return false;
	}
	
	/**
	 * Returns true if the current user can edit this quiz.
	 */
	public function canEdit() {
		if (WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if the current user can see this quiz.
	 * quizzes with period allowed
	 */
	public function canSee() {
		$session = WCF::getSession();
		
		// all for admin
		if ($session->getPermission('admin.community.canManageQuiz')) return true;
		
		// inactive quizzes (only admin, see above)
		if (!$this->isActive) return false;
		
		// only if basic permission
		if (!$session->getPermission('user.quiz.canSee')) return false;
		
		// period
		if ($this->hasPeriod) {
			if ($this->periodStart > TIME_NOW || $this->periodEnd < TIME_NOW) return false;
		}
		
		// quiz conditions
		$conditions = $this->getConditions();
		foreach ($conditions as $condition) {
			if (!$condition->getObjectType()->getProcessor()->checkUser($condition, WCF::getUser())) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns true if the current user can play this quiz.
	 */
	public function canPlay() {
		// permission / guest
		if (!WCF::getSession()->getPermission('user.quiz.canPlay')) return false;
		if (!WCF::getUser()->userID) return false;
		
		// active and period
		if (!$this->isActive) return false;
		$period = $this->getPeriod(); 
		if ($period == 1 || $period == 3) return false;
		
		// play again
		if (!$this->playAgain && $this->hasSolved()) return false;
		
		// quiz conditions
		$conditions = $this->getConditions();
		foreach ($conditions as $condition) {
			if (!$condition->getObjectType()->getProcessor()->checkUser($condition, WCF::getUser())) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns the seconds the current user must pause this quiz.
	 */
	public function mustPause() {
		if ($this->playAgain && $this->paused) {
			$time = $this->getLastSolved(WCF::getUser()->userID) + $this->paused * 60 - TIME_NOW;
			if ($time > 0) return $time;
		}
		
		return 0;
	}
	
	/**
	 * Returns the preview image url.
	 */
	public function getPreviewImage() {
		if ($this->image && file_exists(WCF_DIR.'images/quiz/'.$this->image)) {
			return WCF::getPath().'images/quiz/'.$this->image;
		}
		return WCF::getPath().'images/quiz/default.png';
	}
	
	/**
	 * Returns the preview image path.
	 */
	public function getPreviewImagePath() {
		if ($this->image && file_exists(WCF_DIR.'images/quiz/'.$this->image)) {
			return WCF_DIR.'images/quiz/'.$this->image;
		}
		return WCF_DIR.'images/quiz/default.png';
	}
	
	/**
	 * return status in respect to period
	 * 0: no period
	 * 1: not started
	 * 2: started
	 * 3: ended
	 */
	public function getPeriod() {
		if (!$this->hasPeriod) return 0;
		
		if ($this->periodStart > TIME_NOW) return 1;
		if ($this->periodStart < TIME_NOW && $this->periodEnd > TIME_NOW) return 2;
		return 3;
	}
	
	/**
	 * Returns the conditions of the quiz.
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.uz.wcf.quiz.condition', $this->quizID);
	}
	
	/**
	 * Returns last time the user solved this quiz
	 */
	public function getLastSolved($userID) {
		$sql = "SELECT		time
				FROM		wcf".WCF_N."_quiz_to_user
				WHERE		quizID = ? AND userID = ?
				ORDER BY time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$this->quizID, $userID]);
		if ($row = $statement->fetchArray()) {
			return $row['time'];
		}
		return 0;
	}
	
	/**
	 * Returns success for group
	 */
	public function getSuccess() {
		if (!QUIZ_GROUP_ON || !QUIZ_FILTER_SUCCESS) return true;
		if (!$this->playAgain || empty(unserialize($this->assignGroupIDs))) return true;
		
		$sql = "SELECT	MAX(result) AS success
				FROM	wcf".WCF_N."_quiz_result
				WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([WCF::getUser()->userID]);
		$success = $statement->fetchColumn();
		if ($success === null) return true;
		if ($success < $this->assignResult) return false;
		
		return true;
	}
}
