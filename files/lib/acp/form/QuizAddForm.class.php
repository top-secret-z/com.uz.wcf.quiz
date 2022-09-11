<?php
namespace wcf\acp\form;
use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\PackageCache;
use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizAction;
use wcf\data\quiz\QuizEditor;
use wcf\data\quiz\question\QuestionList;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\QuizNewestBoxCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the quiz add form.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.community.canManageQuiz'];
	
	/**
	 * @inheritDoc
	 */
	public $action = 'add';
	
	/**
	 * quiz related data
	 */
	public $isActive = 1;
	public $showResult = 1;
	public $showResultButton = 1;
	public $showCorrect = 1;
	public $showComment = 0;
	public $showBest = 1;
	public $showStats = 1;
	public $playAgain = 1;
	public $hasPeriod = 0;
	public $periodEnd = '';
	public $periodStart = '';
	public $periodEndObj;
	public $periodStartObj;
	public $timeLimit = 0;
	public $points = 1;
	public $questionIDs = '';
	public $questionList = null;
	public $text = '';
	public $title = '';
	public $showOrder = 99;
	public $paused = 0;
	public $randomize = 0;
	
	/**
	 * user groups
	 */
	protected $availableGroups = null;
	public $assignGroupIDs = [];
	public $assignResult = 90;
	
	// switch for image removal
	public $deleteImage = 0;
	
	/**
	 * temporary image hash
	 */
	public $tmpHash = '';
	
	/**
	 * category
	 */
	public $categoryID = -1;
	public $categoryNodeTree = null;
	public $categoryIDs = [];
	
	/**
	 * list of grouped user group assignment condition object types
	 */
	public $conditions = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('text');
		I18nHandler::getInstance()->register('title');
		
		if (isset($_REQUEST['tmpHash'])) {
			$this->tmpHash = StringUtil::trim($_REQUEST['tmpHash']);
		}
		if (empty($this->tmpHash)) {
			$this->tmpHash = StringUtil::getRandomID();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		if (I18nHandler::getInstance()->isPlainValue('text')) $this->text = I18nHandler::getInstance()->getValue('text');
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
		
		$this->isActive = $this->showResult = $this->showComment = $this->showBest = $this->showStats = 0;
		$this->playAgain = $this->hasPeriod = $this->randomize = 0;
		$this->showCorrect = $this->paused = $this->showResultButton = 0;
		if (isset($_POST['isActive'])) $this->isActive = 1;
		if (isset($_POST['showResult'])) $this->showResult = 1;
		if (isset($_POST['showResultButton'])) $this->showResultButton = 1;
		if (isset($_POST['showCorrect'])) $this->showCorrect = 1;
		if (isset($_POST['showComment'])) $this->showComment = 1;
		if (isset($_POST['showBest'])) $this->showBest = 1;
		if (isset($_POST['showStats'])) $this->showStats = 1;
		if (isset($_POST['playAgain'])) $this->playAgain = 1;
		if (isset($_POST['hasPeriod'])) $this->hasPeriod = 1;
		if (isset($_POST['periodEnd'])) $this->periodEnd = $_POST['periodEnd'];
		if (isset($_POST['periodStart'])) $this->periodStart = $_POST['periodStart'];
		if (isset($_POST['timeLimit'])) $this->timeLimit = intval($_POST['timeLimit']);
		if (isset($_POST['points'])) $this->points = intval($_POST['points']);
		if (isset($_POST['questionIDs'])) $this->questionIDs = StringUtil::trim($_POST['questionIDs']);
		if (isset($_POST['deleteImage'])) $this->deleteImage = 1;
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['paused'])) $this->paused = intval($_POST['paused']);
		if (isset($_POST['randomize'])) $this->randomize = 1;
		
		if (isset($_POST['assignResult'])) $this->assignResult = intval($_POST['assignResult']);
		if (isset($_POST['assignGroupIDs']) && is_array($_POST['assignGroupIDs'])) $this->assignGroupIDs = ArrayUtil::toIntegerArray($_POST['assignGroupIDs']);
		
		$this->periodEndObj = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->periodEnd);
		$this->periodStartObj = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->periodStart);
		
		// conditions
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->readFormParameters();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// title
		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}
		if (mb_strlen($this->title) > 255) {
			throw new UserInputException('title', 'tooLong');
		}
		
		// text
		if (!I18nHandler::getInstance()->validateValue('text')) {
			if (I18nHandler::getInstance()->isPlainValue('text')) {
				throw new UserInputException('text');
			}
			else {
				throw new UserInputException('text', 'multilingual');
			}
		}
		
		// period
		if ($this->hasPeriod) {
			if ($this->periodEndObj === false) {
				throw new UserInputException('periodEnd', 'invalid');
			}
			if ($this->periodStartObj === false) {
				throw new UserInputException('periodStart', 'invalid');
			}
			
			// from before to
			if ($this->periodEndObj->getTimestamp() < $this->periodStartObj->getTimestamp()) {
				throw new UserInputException('periodEnd', 'toBeforeFrom');
			}
			
			// end in past
			if ($this->periodEndObj->getTimestamp() < TIME_NOW) {
				throw new UserInputException('periodEnd', 'inPast');
			}
		}
		
		// questionIDs
		if (empty($this->questionIDs)) {
			throw new UserInputException('questionIDs', 'noQuestions');
		}
		$existings = $this->questionList->getObjects();
		$questionIDs = explode("\n", StringUtil::unifyNewlines(StringUtil::trim($this->questionIDs)));
		$found = [];
		foreach ($questionIDs as $id) {
			if (!array_key_exists($id, $existings)) {
				$found[] = $id;
			}
		}
		if (count($found)) {
			$found = array_unique($found);
			WCF::getTPL()->assign('missingIDs', implode(', ', $found));
			throw new UserInputException('questionIDs', 'missing');
		}
		
		// conditions
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->validate();
			}
		}
		
		// allowed groups
		if (QUIZ_GROUP_ON && count($this->assignGroupIDs)) {
			$allowedGroupIDs = [];
			foreach ($this->availableGroups as $group) {
				$allowedGroupIDs[] = $group->groupID;
			}
			
			if (count(array_diff($this->assignGroupIDs, $allowedGroupIDs))) {
				throw new UserInputException('assignGroupIDs', 'invalidGroup');
			}
		}
		else {
			$this->assignGroupIDs = [];
		}
		
		// paused
		if (!$this->playAgain) {
			$this->paused = 0;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$periodEnd = $this->periodEndObj ? $this->periodEndObj->getTimestamp() : 0;
		$periodStart = $this->periodStartObj ? $this->periodStartObj->getTimestamp() : 0;
		
		$questionIDs = explode("\n", StringUtil::unifyNewlines(StringUtil::trim($this->questionIDs)));
		
		// save quiz
		$this->objectAction = new QuizAction([], 'create', [
				'data' => array_merge($this->additionalFields, [
						'text' => $this->text,
						'title' => $this->title,
						'isActive' => $this->isActive,
						'showBest' => $this->showBest,
						'showComment' => $this->showComment,
						'showCorrect' => $this->showCorrect,
						'showResult' => $this->showResult,
						'showResultButton' => $this->showResultButton,
						'showStats' => $this->showStats,
						'playAgain' => $this->playAgain,
						'hasPeriod' => $this->hasPeriod,
						'periodEnd' => $periodEnd,
						'periodStart' => $periodStart,
						'timeLimit' => $this->timeLimit,
						'points' => $this->points,
						'time' => TIME_NOW,
						'userID' => WCF::getUser()->userID,
						'username' => WCF::getUser()->username,
						'questions' => count($questionIDs),
						'showOrder' => $this->showOrder,
						'paused' => $this->paused,
						'randomize' => $this->randomize,
						'assignResult' => $this->assignResult,
						'assignGroupIDs' => serialize($this->assignGroupIDs),
						'groupIDs' => serialize([])
				]),
				'tmpHash' => $this->tmpHash
		]);
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		
		$quizEditor = new QuizEditor($returnValues['returnValues']);
		$quizID = $returnValues['returnValues']->quizID;
		
		if (!I18nHandler::getInstance()->isPlainValue('text')) {
			I18nHandler::getInstance()->save('text', 'wcf.acp.quiz.text' . $quizID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$quizEditor->update(['text' => 'wcf.acp.quiz.text' . $quizID]);
		}
		
		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'wcf.acp.quiz.title' . $quizID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$quizEditor->update(['title' => 'wcf.acp.quiz.title' . $quizID]);
		}
		
		// save quiz_to_question
		$sql = "DELETE FROM	wcf".WCF_N."_quiz_to_question
				WHERE		quizID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$quizID]);
		
		WCF::getDB()->beginTransaction();
		$sql = "INSERT INTO	wcf".WCF_N."_quiz_to_question
					(quizID, questionID)
				VALUES		(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($questionIDs as $id) {
			$statement->execute([$quizID, $id]);
		}
		WCF::getDB()->commitTransaction();
		
		// reset user storage
		UserStorageHandler::getInstance()->resetAll('unplayedQuizzes');
		
		// transform conditions array into one-dimensional array
		$conditions = [];
		foreach ($this->conditions as $groupedObjectTypes) {
			$conditions = array_merge($conditions, $groupedObjectTypes);
		}
		
		ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->quizID, $conditions);
		
		$this->saved();
		
		// reset values
		$this->hasPeriod = 0;
		$this->isActive = 1;
		$this->showResult = 1;
		$this->showResultButton = 1;
		$this->showCorrect = 1;
		$this->showComment = 0;
		$this->showBest = 1;
		$this->showStats = 1;
		$this->playAgain = 1;
		$this->periodEnd = '';
		$this->periodStart = '';
		$this->timeLimit = 0;
		$this->points = 1;
		$this->questionIDs = '';
		$this->text = '';
		$this->title = '';
		$this->showOrder = 99;
		$this->paused = 0;
		$this->randomize = 0;
		$this->categoryIDs = [];
		
		$this->assignGroupIDs = [];
		$this->assignResult = 90;
		
		I18nHandler::getInstance()->reset();
		
		// remove existing image if desired
		if ($this->deleteImage) {
			$quiz = new Quiz($quizID);
			@unlink(WCF_DIR.'images/quiz/'.$quiz->image);
			$quizEditor->update(['image' => '']);
		}
		
		// reset conditions
		foreach ($this->conditions as $conditions) {
			foreach ($conditions as $condition) {
				$condition->getProcessor()->reset();
			}
		}
		
		// update caches
		QuizNewestBoxCacheBuilder::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign([
				'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		// question(s) must exist
		$this->questionList = new QuestionList();
		$this->questionList->getConditionBuilder()->add('quiz_question.approved = 1');
		$this->questionList->readObjects();
		
		// categories
		$this->categoryNodeTree = new CategoryNodeTree('com.uz.wcf.quiz.category', 0, true);
		
		// get accessible groups, exclude admin/owner group (no OWNER in 3.1)
		$this->availableGroups = UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS));
		foreach ($this->availableGroups as $key => $group) {
			if ($group->isAdminGroup()) {
				unset($this->availableGroups[$key]);
			}
		}
		
		// conditions
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.uz.wcf.quiz.condition');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($groupedObjectTypes[$objectType->conditiongroup])) {
				$groupedObjectTypes[$objectType->conditiongroup] = [];
			}
			
			$groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
		$this->conditions = $groupedObjectTypes;
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
				'action' => $this->action,
				
				'groupedObjectTypes' => $this->conditions,
				'questionList' => $this->questionList,
				
				'hasPeriod' => $this->hasPeriod,
				'isActive' => $this->isActive,
				'showResult' => $this->showResult,
				'showResultButton' => $this->showResultButton,
				'showCorrect' => $this->showCorrect,
				'showComment' => $this->showComment,
				'showBest' => $this->showBest,
				'showStats' => $this->showStats,
				'playAgain' => $this->playAgain,
				'periodEnd' => $this->periodEnd,
				'periodStart' => $this->periodStart,
				'timeLimit' => $this->timeLimit,
				'points' => $this->points,
				'questionIDs' => $this->questionIDs,
				'tmpHash' => $this->tmpHash,
				'showOrder' => $this->showOrder,
				'paused' => $this->paused,
				'randomize' => $this->randomize,
				'categoryNodeList' => $this->categoryNodeTree->getIterator(),
				'categoryID' => $this->categoryID,
				'categoryIDs' => $this->categoryIDs,
				
				'availableGroups' => $this->availableGroups,
				'assignGroupIDs' => $this->assignGroupIDs,
				'assignResult' => $this->assignResult
		]);
	}
}
