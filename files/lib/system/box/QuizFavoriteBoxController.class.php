<?php
namespace wcf\system\box;
use wcf\data\quiz\QuizViewableFavoriteList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows most favorite quizzes.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizFavoriteBoxController extends AbstractDatabaseObjectListBoxController {
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
		return new QuizViewableFavoriteList();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxQuizFavorite', 'wcf', [
				'quizzes' => $this->objectList
		]);
	}
}
