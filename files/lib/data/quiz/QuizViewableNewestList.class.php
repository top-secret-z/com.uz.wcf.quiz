<?php
namespace wcf\data\quiz;
use wcf\system\cache\builder\QuizNewestBoxCacheBuilder;

/**
 * Represents a list of newest quizzes viewable to user.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizViewableNewestList extends QuizList {
	/**
	 * Creates a new list object with max 15 items
	 */
	public function __construct() {
		parent::__construct();
		
		// get quiz ids
		$quizList = QuizNewestBoxCacheBuilder::getInstance()->getData();
		$quizIDs = [];
		$count = 0;
		foreach ($quizList as $quiz) {
			if ($quiz->canSee()) {
				$quizIDs[] = $quiz->quizID;
				$count ++;
				if ($count > 15) break;
			}
		}
		
		// get quizzes
		if (!empty($quizIDs)) {
			$this->getConditionBuilder()->add("quizID IN (?)", [$quizIDs]);
			$this->sqlOrderBy = 'time DESC';
		}
		else {
			$this->getConditionBuilder()->add("1=0");
		}
	}
}
