<?php
namespace wcf\system\box;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\QuizBestSolverBoxCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows users with best quiz results.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizBestSolverBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 5;
	public $maximumLimit = 15;
	public $minimumLimit = 1;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [];
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = '';
	
	/**
	 * users loaded from cache
	 */
	public $users = [];
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Quiz');
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if (!MODULE_UZQUIZ || !WCF::getSession()->getPermission('user.quiz.canSee')) {
			return false;
		}
		
		parent::hasContent();
		
		return count($this->users) > 0;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		$this->readObjects();
		
		$this->content = $this->getTemplate();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		EventHandler::getInstance()->fireAction($this, 'readObjects');
		
		$userIDs = QuizBestSolverBoxCacheBuilder::getInstance()->getData();
		
		if (!empty($userIDs)) {
			$this->users = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
			
			// sort users
			if (!empty($this->users)) {
				DatabaseObject::sort($this->users, 'uzQuizRate', 'DESC');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxQuizBestSolver', 'wcf', [
				'users' => $this->users
		]);
	}
}
