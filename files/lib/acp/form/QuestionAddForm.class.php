<?php
namespace wcf\acp\form;
use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\data\package\PackageCache;
use wcf\data\quiz\question\Question;
use wcf\data\quiz\question\QuestionAction;
use wcf\data\quiz\question\QuestionEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the question add form.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuestionAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.question.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.community.canManageQuiz'];
	
	/**
	 * question and answers
	 */
	public $question = '';
	public $answerOne = '';
	public $answerTwo = '';
	public $answerThree = '';
	public $answerFour = '';
	public $answerFive = '';
	public $answerSix = '';
	public $comment = '';
	
	// switch for image removal
	public $deleteImage = 0;
	
	/**
	 * index of correct answer and answer count
	 */
	public $correct = 0;
	public $count = 1;
	
	/**
	 * temporary image hash
	 */
	public $tmpHash = '';
	
	/**
	 * category
	 */
	public $categoryID = 0;
	public $categoryNodeTree = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('question');
		I18nHandler::getInstance()->register('answerOne');
		I18nHandler::getInstance()->register('answerTwo');
		I18nHandler::getInstance()->register('answerThree');
		I18nHandler::getInstance()->register('answerFour');
		I18nHandler::getInstance()->register('answerFive');
		I18nHandler::getInstance()->register('answerSix');
		I18nHandler::getInstance()->register('comment');
		
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
		
		if (I18nHandler::getInstance()->isPlainValue('question')) $this->question = I18nHandler::getInstance()->getValue('question');
		if (I18nHandler::getInstance()->isPlainValue('answerOne')) $this->answerOne = I18nHandler::getInstance()->getValue('answerOne');
		if (I18nHandler::getInstance()->isPlainValue('answerTwo')) $this->answerTwo = I18nHandler::getInstance()->getValue('answerTwo');
		if (I18nHandler::getInstance()->isPlainValue('answerThree')) $this->answerThree = I18nHandler::getInstance()->getValue('answerThree');
		if (I18nHandler::getInstance()->isPlainValue('answerFour')) $this->answerFour = I18nHandler::getInstance()->getValue('answerFour');
		if (I18nHandler::getInstance()->isPlainValue('answerFive')) $this->answerFive = I18nHandler::getInstance()->getValue('answerFive');
		if (I18nHandler::getInstance()->isPlainValue('answerSix')) $this->answerSix = I18nHandler::getInstance()->getValue('answerSix');
		if (I18nHandler::getInstance()->isPlainValue('comment')) $this->comment = I18nHandler::getInstance()->getValue('comment');
		
		if (isset($_POST['correct'])) $this->correct = intval($_POST['correct']);
		if (isset($_POST['deleteImage'])) $this->deleteImage = 1;
		if (isset($_POST['categoryID'])) $this->categoryID = intval($_POST['categoryID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// question
		if (!I18nHandler::getInstance()->validateValue('question')) {
			if (I18nHandler::getInstance()->isPlainValue('question')) {
				throw new UserInputException('question');
			}
			else {
				throw new UserInputException('question', 'multilingual');
			}
		}
		
		// first 2 answers must exist
		if (!I18nHandler::getInstance()->validateValue('answerOne')) {
			if (I18nHandler::getInstance()->isPlainValue('answerOne')) {
				throw new UserInputException('answerOne');
			}
			else {
				throw new UserInputException('answerOne', 'multilingual');
			}
		}
		if (!I18nHandler::getInstance()->validateValue('answerTwo')) {
			if (I18nHandler::getInstance()->isPlainValue('answerTwo')) {
				throw new UserInputException('answerTwo');
			}
			else {
				throw new UserInputException('answerTwo', 'multilingual');
			}
		}
		
		// next answers may be empty
		if (!I18nHandler::getInstance()->validateValue('answerThree')) {
			if (!I18nHandler::getInstance()->isPlainValue('answerThree')) {
				throw new UserInputException('answerThree', 'multilingual');
			}
		}
		if (!I18nHandler::getInstance()->validateValue('answerFour')) {
			if (!I18nHandler::getInstance()->isPlainValue('answerFour')) {
				throw new UserInputException('answerFour', 'multilingual');
			}
		}
		if (!I18nHandler::getInstance()->validateValue('answerFive')) {
			if (!I18nHandler::getInstance()->isPlainValue('answerFive')) {
				throw new UserInputException('answerFive', 'multilingual');
			}
		}
		if (!I18nHandler::getInstance()->validateValue('answerSix')) {
			if (!I18nHandler::getInstance()->isPlainValue('answerSix')) {
				throw new UserInputException('answerSix', 'multilingual');
			}
		}
		
		if (!I18nHandler::getInstance()->validateValue('comment')) {
			if (!I18nHandler::getInstance()->isPlainValue('comment')) {
				throw new UserInputException('comment', 'multilingual');
			}
		}
		
		// check answers 3 to six empty
		$empty3 = $empty4 = $empty5 = $empty6 = 1;
		
		if (I18nHandler::getInstance()->isPlainValue('answerThree')) {
			if (!empty($this->answerThree)) $empty3 = 0;
		}
		else $empty3 = 0;
		
		if (I18nHandler::getInstance()->isPlainValue('answerFour')) {
			if (!empty($this->answerFour)) $empty4 = 0;
		}
		else $empty4 = 0;
		
		if (I18nHandler::getInstance()->isPlainValue('answerFive')) {
			if (!empty($this->answerFive)) $empty5 = 0;
		}
		else $empty5 = 0;
		
		if (I18nHandler::getInstance()->isPlainValue('answerSix')) {
			if (!empty($this->answerSix)) $empty6 = 0;
		}
		else $empty6 = 0;
		
		// no gaps
		if ($empty3 && (!$empty4 || !$empty5 || !$empty6)) {
			throw new UserInputException('answerThree', 'missing');
		}
		if ($empty4 && (!$empty5 || !$empty6)) {
			throw new UserInputException('answerFour', 'missing');
		}
		if ($empty5 && !$empty6) {
			throw new UserInputException('answerFive', 'missing');
		}
		
		// correct answer
		$this->count = 2;
		if (!$empty3) $this->count ++;
		if (!$empty4) $this->count ++;
		if (!$empty5) $this->count ++;
		if (!$empty6) $this->count ++;
		
		if ($this->correct == 0 || $this->correct > $this->count) {
			throw new UserInputException('correct', 'incorrect');
		}
		
		// category
		if ($this->categoryID) {
			$category = new Category($this->categoryID);
			if (!$category->categoryID) {
				throw new UserInputException('categoryID', 'notValid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save question
		$this->objectAction = new QuestionAction([], 'create', [
				'data' => array_merge($this->additionalFields, [
						'question' => $this->question,
						'answerOne' => $this->answerOne,
						'answerTwo' => $this->answerTwo,
						'answerThree' => $this->answerThree,
						'answerFour' => $this->answerFour,
						'answerFive' => $this->answerFive,
						'answerSix' => $this->answerSix,
						'comment' => $this->comment,
						'correct' => $this->correct,
						'count' => $this->count,
						'time' => TIME_NOW,
						'userID' => WCF::getUser()->userID,
						'username' => WCF::getUser()->username,
						'isACP' => 1,
						'approved' => 1,
						'categoryID' => $this->categoryID ? $this->categoryID : null
				]),
				'tmpHash' => $this->tmpHash
		]);
		$this->objectAction->executeAction();
		
		$returnValues = $this->objectAction->getReturnValues();
		$questionEditor = new QuestionEditor($returnValues['returnValues']);
		$questionID = $returnValues['returnValues']->questionID;
		
		if (!I18nHandler::getInstance()->isPlainValue('question')) {
			I18nHandler::getInstance()->save('question', 'wcf.acp.quiz.question' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['question' => 'wcf.acp.quiz.question' . $questionID]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('answerOne')) {
			I18nHandler::getInstance()->save('answerOne', 'wcf.acp.quiz.answerOne' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['answerOne' => 'wcf.acp.quiz.answerOne' . $questionID]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('answerTwo')) {
			I18nHandler::getInstance()->save('answerTwo', 'wcf.acp.quiz.answerTwo' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['answerTwo' => 'wcf.acp.quiz.answerTwo' . $questionID]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('answerThree')) {
			I18nHandler::getInstance()->save('answerThree', 'wcf.acp.quiz.answerThree' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['answerThree' => 'wcf.acp.quiz.answerThree' . $questionID]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('answerFour')) {
			I18nHandler::getInstance()->save('answerFour', 'wcf.acp.quiz.answerFour' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['answerFour' => 'wcf.acp.quiz.answerFour' . $questionID]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('answerFive')) {
			I18nHandler::getInstance()->save('answerFive', 'wcf.acp.quiz.answerFive' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['answerFive' => 'wcf.acp.quiz.answerFive' . $questionID]);
		}
		if (!I18nHandler::getInstance()->isPlainValue('answerSix')) {
			I18nHandler::getInstance()->save('answerSix', 'wcf.acp.quiz.answerSix' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['answerSix' => 'wcf.acp.quiz.answerSix' . $questionID]);
		}
		
		if (!I18nHandler::getInstance()->isPlainValue('comment')) {
			I18nHandler::getInstance()->save('comment', 'wcf.acp.quiz.comment' . $questionID, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
			$questionEditor->update(['comment' => 'wcf.acp.quiz.comment' . $questionID]);
		}
		
		$this->saved();
		
		// reset values
		$this->question = $this->answerOne = $this->answerTwo = $this->answerThree = '';
		$this->answerFour = $this->answerFive = $this->answerSix = $this->comment = '';
		$this->correct = 0;
		$this->categoryID = 0;
		
		I18nHandler::getInstance()->reset();
		
		// remove existing image if desired
		if ($this->deleteImage) {
			$question = new Question($questionID);
			@unlink(WCF_DIR.'images/quiz/question/'.$question->image);
			$questionEditor->update(['image' => '']);
		}
		
		// show success
		WCF::getTPL()->assign([
				'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		// categories
		$this->categoryNodeTree = new CategoryNodeTree('com.uz.wcf.quiz.category', 0, false);
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'add',
				'correct' => $this->correct,
				'tmpHash' => $this->tmpHash,
				'categoryNodeList' => $this->categoryNodeTree->getIterator(),
				'categoryID' => $this->categoryID
		]);
	}
}
