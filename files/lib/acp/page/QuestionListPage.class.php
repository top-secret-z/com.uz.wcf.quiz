<?php
namespace wcf\acp\page;
use wcf\data\category\CategoryList;
use wcf\data\quiz\question\QuestionList;
use wcf\page\SortablePage;
use wcf\system\category\CategoryHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the Question list page.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuestionListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.question.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'questionID';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['questionID', 'categoryID', 'question', 'count', 'username', 'image', 'time'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.community.canManageQuiz'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = QuestionList::class;
	
	/**
	 * category list
	 */
	public $categories = null;
	
	/**
	 * filter data
	 */
	public $filter = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		// Filter form
		if (!empty($_REQUEST['filter'])) {
			$this->filter = StringUtil::trim($_REQUEST['filter']);
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
	}
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		// get searchable data, if filter
		if (!empty($this->filter)) {
			$languages = LanguageFactory::getInstance()->getLanguages();
			$questionIDs = [0];
			
			$sql = "SELECT		question.*, category.title
					FROM 		wcf".WCF_N."_quiz_question question
					LEFT JOIN 	wcf".WCF_N."_category category ON (question.categoryID = category.categoryID)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				$search = '';
				foreach ($languages as $language) {
					$search .= ' ' . $language->get($row['question']);
					if (QUIZ_CATEGORY_ON) {
						if ($row['title']) $search .= ' ' . $language->get($row['title']);
						else $search .= ' ' . $language->get('wcf.acp.quiz.question.categoryID.default');
					}
					$search .= ' ' . $row['username'];
					$search .= ' ' . $row['answerOne'];
					$search .= ' ' . $row['answerTwo'];
					$search .= ' ' . $row['answerThree'];
					$search .= ' ' . $row['answerFour'];
					$search .= ' ' . $row['answerFive'];
					$search .= ' ' . $row['answerSix'];
				}
				if (mb_stripos($search, $this->filter) !== false) $questionIDs[] = $row['questionID'];
			}
		}
		
		parent::initObjectList();
		
		if (!empty($this->filter)) {
			$this->objectList->getConditionBuilder()->add('questionID IN (?)', [$questionIDs]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign sorting parameters
		WCF::getTPL()->assign([
				'categories' => $this->categories,
				'filter' => $this->filter
		]);
	}
}
