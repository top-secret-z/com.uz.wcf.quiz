<?php
namespace wcf\data\quiz;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IFeedEntryWithEnclosure;
use wcf\data\TUserContent;
use wcf\system\feed\enclosure\FeedEnclosure;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a Quiz for RSS feeds.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
class FeedQuiz extends DatabaseObjectDecorator implements IFeedEntryWithEnclosure {
	use TUserContent;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Quiz::class;
	
	/**
	 * @var FeedEnclosure
	 */
	protected $enclosure;
	
	/**
	 * @getLink
	 */
	public function getLink() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @getTitle
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @getFormattedMessage
	 */
	public function getFormattedMessage() {
		return $this->getDecoratedObject()->getText();
	}
	
	/**
	 * @getMessage
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getText();
	}
	
	/**
	 * @getExcerpt
	 */
	public function getExcerpt($maxLength = 255) {
		return StringUtil::encodeHTML($this->getDecoratedObject()->getText());
	}
	
	/**
	 * @__toString
	 */
	public function __toString() {
		return $this->getMessage();
	}
	
	/**
	 * @getComments
	 */
	public function getComments() {
		return '';
	}
	
	/**
	 * @getComments
	 */
	public function getUsername() {
		$this->getDecoratedObject()->username;
	}
	
	/**
	 * @getCategories
	 */
	public function getCategories() {
		return [];
	}
	
	/**
	 * @getTime
	 */
	public function getTime() {
		return $this->getDecoratedObject()->getTime();
	}
	
	/**
	 * @isVisible
	 */
	public function isVisible() {
		return $this->getDecoratedObject()->canSee();
	}
	
	/**
	 * @getEnclosure
	 */
	public function getEnclosure() {
		if ($this->enclosure === null) {
			$url = $this->getDecoratedObject()->getPreviewImage();
			$path = $this->getDecoratedObject()->getPreviewImagePath();
			$this->enclosure = new FeedEnclosure($url, FileUtil::getMimeType($path), filesize($path));
		}
		
		return $this->enclosure;
	}
}
