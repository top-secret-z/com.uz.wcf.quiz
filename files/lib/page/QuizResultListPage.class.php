<?php
namespace wcf\page;
use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizResultList;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Shows the user's quiz result page.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizResultListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'com.uz.wcf.quiz.QuizResultListPage';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_UZQUIZ'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.quiz.canPlay'];
	
	/**
	 * @inheritDoc
	 */
	public $enableTracking = true;
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 15;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = QuizResultList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['time', 'quizTitle', 'result'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * result stats
	 */
	public $stats = [];
	public $userID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// 	add breadcrumbs
		if (MODULE_UZQUIZ) PageLocationManager::getInstance()->addParentLocation('com.uz.wcf.quiz.QuizPage');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->userID = WCF::getUser()->userID;
		
		$this->objectList->getConditionBuilder()->add("userID = ?", [$this->userID]);
		
		// stats
		$this->stats = $this->objectList->getStats($this->userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables () {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'userID' => $this->userID,
				'stats' => $this->stats
		]);
	}
}
