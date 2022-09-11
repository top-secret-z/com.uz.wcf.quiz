<?php
namespace wcf\data\quiz\question;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a Question.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class Question extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'quiz_question';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'questionID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->question;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('QuizQuestionList', [
				'object' => $this,
				'questionID' => $this->questionID,
				'forceFrontend' => true
		], '#question'.$this->questionID);
	}
	
	/**
	 * get question as array in user's language
	 */
	public function getQuestion() {
		$answers = [];
		$answers[] = WCF::getLanguage()->get($this->question->answerOne);
		$answers[] = WCF::getLanguage()->get($this->question->answerTwo);
		if (!empty($this->question->answerThree)) $answers[] = WCF::getLanguage()->get($this->question->answerThree);
		if (!empty($this->question->answerFour)) $answers[] = WCF::getLanguage()->get($this->question->answerFour);
		if (!empty($this->question->answerFive)) $answers[] = WCF::getLanguage()->get($this->question->answerFive);
		if (!empty($this->question->answerSix)) $answers[] = WCF::getLanguage()->get($this->question->answerSix);
		return [
				'question' => WCF::getLanguage()->get($this->question),
				'correct' => $this->question->correct,
				'answers' => $answers
		];
	}
	
	/**
	 * returns 0 if not used by a quiz
	 */
	public function isUsedByQuiz() {
		$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_quiz_to_question
				WHERE	questionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->questionID]);
		$row = $statement->fetchArray();
		return $row['count'];
	}
	
	/**
	 * Returns the preview image path.
	 */
	public function getPreviewImage() {
		if ($this->image && file_exists(WCF_DIR.'images/quiz/question/'.$this->image)) {
			return WCF::getPath().'images/quiz/question/'.$this->image;
		}
		
		return '';
	}
	
	/**
	 * Returns true if the current user can edit this question.
	 */
	public function canEdit() {
		if (WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
			return true;
		}
		
		if (!$this->isACP && $this->approved) {
			return false;
		}
		
		if (WCF::getSession()->getPermission('user.quiz.canSubmitQuestions') && $this->userID == WCF::getUser()->userID) {
			return true;
		}
		
		return false;
	}
}
