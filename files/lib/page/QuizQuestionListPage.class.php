<?php
namespace wcf\page;
use wcf\data\category\CategoryList;
use wcf\data\quiz\question\Question;
use wcf\data\quiz\question\QuestionList;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Shows the questions of a user.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizQuestionListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'com.uz.wcf.quiz.QuizQuestionListPage';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_UZQUIZ'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.quiz.canSubmitQuestions'];
	
	/**
	 * @inheritDoc
	 */
	public $enableTracking = true;
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 10;
	
	/**
	 * user 
	 */
	public $userID = 0;
	public $user = null;
	
	/**
	 * question
	 */
	public $question = null;
	public $questionID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = QuestionList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['time', 'question', 'categoryID', 'count'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * category list
	 */
	public $categories = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		$this->user = WCF::getUser();
		
		if (isset($_REQUEST['questionID'])) $this->questionID = intval($_REQUEST['questionID']);
		if ($this->questionID) {
			$this->question = new Question($this->questionID);
			if (!$this->question->questionID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('quiz_question.isACP = 0');
		$this->objectList->getConditionBuilder()->add('quiz_question.userID = ?', [$this->user->userID]);
		$this->objectList->sqlOrderBy = 'time DESC';
		
		// calculate pageNo, if questionID is set
		if ($this->questionID) {
			$sql = "SELECT	COUNT(*) AS counter
					FROM	wcf".WCF_N."_quiz_question
					WHERE	isACP = 0 AND userID = ? AND time > ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->user->userID, $this->question->time]);
			$row = $statement->fetchArray();
			$this->pageNo = intval(ceil(($row['counter']+1) / $this->itemsPerPage));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$objectType = CategoryHandler::getInstance()->getObjectTypeByName('com.uz.wcf.quiz.category');
		if ($objectType) {
			$categoryList = new CategoryList();
			$categoryList->getConditionBuilder()->add('category.objectTypeID = ?', [$objectType->objectTypeID]);
			$categoryList->readObjects();
			$this->categories = $categoryList->getObjects();
		}
		
		// 	add breadcrumbs
		if (MODULE_UZQUIZ) PageLocationManager::getInstance()->addParentLocation('com.uz.wcf.quiz.QuizPage');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables () {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'allowSpidersToIndexThisPage' => true,
				'user' => $this->user,
				'userID' => $this->userID,
				'categories' => $this->categories
		]);
	}
}
