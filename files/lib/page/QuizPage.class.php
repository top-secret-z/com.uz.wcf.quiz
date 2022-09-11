<?php
namespace wcf\page;
use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the start page of the quiz.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.user.quiz.menu';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_UZQUIZ'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.quiz.canSee'];
	
	/**
	 * @inheritDoc
	 */
	public $enableTracking = true;
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 10;
	
	/**
	 * quiz and filter
	 */
	public $quiz = null;
	public $quizID = 0;
	// 0 = all, 1 = unplayed, 2 = played
	public $filter = 0;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = QuizList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['time', 'questions', 'counter', 'title', 'timeLimit', 'showOrder', 'ratingTotal'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = QUIZ_DEFAULT_SORT_FIELD;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = QUIZ_DEFAULT_SORT_ORDER;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['quizID'])) $this->quizID = intval($_REQUEST['quizID']);
		if ($this->quizID) {
			$this->quiz = new Quiz($this->quizID);
			if (!$this->quiz->quizID) {
				throw new IllegalLinkException();
			}
		}
		
		// read filter
		if (isset($_REQUEST['filter'])) {
			$this->filter = intval($_REQUEST['filter']);
			if ($this->filter < 0 || $this->filter > 2) $this->filter = 0;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		// check for permission
		$ids = [];
		$count = 0;
		$ids[] = 0;
		$list = new QuizList();
		$list->readObjects();
		foreach ($list->getObjects() as $quiz) {
			if ($quiz->canSee()) {
				// filter
				if (QUIZ_FILTER && $this->filter > 0) {
					$solved = $quiz->hasSolved();
					
					if ($this->filter == 1 && $solved == true) {
						if (!QUIZ_FILTER_SUCCESS) continue;
						if ($quiz->getSuccess()) continue;
					}
					
					if ($this->filter == 2 && $solved == false) {
						continue;
					}
				}
				$ids[] = $quiz->quizID;
				
				// count quizzes for pageNo; must be younger than selected quiz (sortorder time desc)
				if ($this->quizID) {
					if ($quiz->time > $this->quiz->time) $count ++;
				}
			}
		}
		
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("quiz.quizID IN (?)", [$ids]);
		
		// calculate pageNo, if quizID is set
		if ($this->quizID && $count) {
			$this->pageNo = intval(ceil($count/ $this->itemsPerPage));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign filter
		WCF::getTPL()->assign([
				'filter' => $this->filter
		]);
	}
}
