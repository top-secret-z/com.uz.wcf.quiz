<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Daily Quiz Cleanup
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class QuizCleanupCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// delete displayed / old comments
		$sql = "DELETE FROM	wcf".WCF_N."_quiz_to_user_comment
				WHERE		displayed = ? OR time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([1, TIME_NOW - 86400 * 180]);
		
		// cleanup temp table after 21 days
		$sql = "DELETE FROM	wcf".WCF_N."_quiz_temp
				WHERE		time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([TIME_NOW - 86400 * 21]);
	}
}
