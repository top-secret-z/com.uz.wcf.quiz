<?php
namespace wcf\acp\form;
use wcf\acp\form\AbstractCategoryEditForm;

/**
 * Shows the Quiz category edit form.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.wcf.quiz.category';
	
	/**
	 * @inheritDoc
	 */
	public $pageTitle = 'wcf.acp.menu.link.quiz.category.list';
}
