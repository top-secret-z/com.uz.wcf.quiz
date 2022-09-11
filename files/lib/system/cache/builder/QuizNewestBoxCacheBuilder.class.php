<?php
namespace wcf\system\cache\builder;
use wcf\data\quiz\QuizList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * Caches the newest quizzes.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizNewestBoxCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		if (!MODULE_UZQUIZ) return array();
		
		$quizList = new QuizList();
		$quizList->getConditionBuilder()->add("isActive = 1");
		$quizList->getConditionBuilder()->add('(hasPeriod = ? OR (periodStart < ? && periodEnd > ?))', array(0, TIME_NOW, TIME_NOW));
		$quizList->sqlOrderBy = 'time DESC';
		$quizList->sqlLimit = 100;
		$quizList->readObjects();
		
		return $quizList->getObjects();
	}
}
