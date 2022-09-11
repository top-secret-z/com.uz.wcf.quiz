<?php
namespace wcf\acp\form;
use wcf\acp\form\AbstractCategoryAddForm;

/**
 * Shows the Quiz category add form.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.wcf.quiz.category';
	
	/**
	 * @inheritDoc
	 */
	public $pageTitle = 'wcf.acp.menu.link.quiz.category.add';
}
