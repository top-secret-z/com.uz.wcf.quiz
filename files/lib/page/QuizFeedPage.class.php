<?php
namespace wcf\page;
use wcf\data\quiz\FeedQuizList;
use wcf\system\WCF;

/**
 * Shows quizzes  in feed.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizFeedPage extends AbstractFeedPage {
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// read the quizzes
		$this->items = new FeedQuizList();
		$this->items->readObjects();
		
		$this->title = WCF::getLanguage()->get('wcf.user.quiz.quiz');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		foreach ($this->items as $item) {
			$enclosure = $item->getEnclosure();
		}
		
		WCF::getTPL()->assign([
				'supportsEnclosure' => true
		]);
	}
}
