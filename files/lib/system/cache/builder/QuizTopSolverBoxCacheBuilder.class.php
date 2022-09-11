<?php
namespace wcf\system\cache\builder;
use wcf\data\user\UserList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * Caches the top quiz users.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizTopSolverBoxCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		if (!MODULE_UZQUIZ) return array();
		
		$userList = new UserList();
		$userList->getConditionBuilder()->add('user_table.uzQuiz > 0');
		$userList->sqlOrderBy = 'user_table.uzQuiz DESC';
		$userList->sqlLimit = 15;
		$userList->readObjectIDs();
		
		return $userList->getObjectIDs();
	}
}
