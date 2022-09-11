<?php
namespace wcf\acp\page;
use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizResultList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the quiz stats page
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizStatsPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.community.canManageQuiz'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 25;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'resultID';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['resultID', 'time', 'username', 'result'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = QuizResultList::class;
	
	// data
	public $quiz = null;
	public $stats = [];
	
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!isset($_REQUEST['id'])) {
			throw new IllegalLinkException();
		}
		
		if (!empty($_REQUEST['id'])) {
			$id = intval($_REQUEST['id']);
			$this->quiz = new Quiz($id);
			if (!$this->quiz->quizID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		// quiz
		$this->objectList->getConditionBuilder()->add('quizID = ?', [$this->quiz->quizID]);
		
		// stats
		$this->stats = [];
		$questions = $this->quiz->questions;
		
		// average rate, users
		$sql = "SELECT	COALESCE(SUM(total), 0) AS total, COALESCE(SUM(correct), 0) AS correct, COUNT(DISTINCT userID) AS users
				FROM	wcf".WCF_N."_quiz_to_user
				WHERE	quizID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->quiz->quizID]);
		$row = $statement->fetchArray();
		
		$average = 0;
		if ($row['total']) {
			$average = $row['correct'] / $row['total'] * 100;
		}
		$users = $row['users'];
		
		$language = WCF::getLanguage();
		
		$this->stats['questions'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.questions', ['value' => $questions]);
		$this->stats['users'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.users', ['value' => $users]);
		$this->stats['average'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.average', ['value' => $average]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$objects = $this->objectList->getObjects();
		if (count($objects)) {
			$userIDs = [];
			foreach ($objects as $result) {
				$result->user = UserProfileRuntimeCache::getInstance()->getObject($result->userID);
			}
		}
		
		WCF::getTPL()->assign([
				'quiz' => $this->quiz,
				'stats' => $this->stats
		]);
	}
}
