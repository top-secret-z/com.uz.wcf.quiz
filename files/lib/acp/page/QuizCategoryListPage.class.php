<?php
namespace wcf\acp\page;
use wcf\acp\page\AbstractCategoryListPage;

/**
 * Shows the Quiz category list page.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizCategoryListPage extends AbstractCategoryListPage {
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
