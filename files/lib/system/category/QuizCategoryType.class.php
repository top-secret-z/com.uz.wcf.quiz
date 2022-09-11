<?php
namespace wcf\system\category;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type for Quiz.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $hasDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'wcf.uz.quiz.category';
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 0;
	
	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		return 'wcf';
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.community.canManageQuiz');
	}
}
