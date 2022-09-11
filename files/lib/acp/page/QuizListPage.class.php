<?php
namespace wcf\acp\page;
use wcf\data\quiz\QuizList;
use wcf\page\SortablePage;

/**
 * Lists the configured Quizes
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.quiz.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'quizID';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['quizID', 'hasPeriod', 'title', 'counter', 'questions', 'time', 'showOrder'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.community.canManageQuiz'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = QuizList::class;
}
