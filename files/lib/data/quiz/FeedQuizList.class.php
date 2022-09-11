<?php
namespace wcf\data\quiz;

/**
 * Represents a list of Quizzes for RSS feeds.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class FeedQuizList extends QuizViewableNewestList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = FeedQuiz::class;
}
