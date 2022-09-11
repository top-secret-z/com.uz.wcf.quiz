<?php
namespace wcf\system\box;
use wcf\data\quiz\QuizViewableNewestList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows newest quizzes.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizNewestBoxController extends AbstractDatabaseObjectListBoxController {
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
		return new QuizViewableNewestList();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxQuizNewest', 'wcf', [
				'quizzes' => $this->objectList
		]);
	}
}
