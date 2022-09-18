<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
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
 */
class FeedQuiz extends DatabaseObjectDecorator implements IFeedEntryWithEnclosure
{
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
    public function getLink()
    {
        return $this->getDecoratedObject()->getLink();
    }

    /**
     * @getTitle
     */
    public function getTitle()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @getFormattedMessage
     */
    public function getFormattedMessage()
    {
        return $this->getDecoratedObject()->getText();
    }

    /**
     * @getMessage
     */
    public function getMessage()
    {
        return $this->getDecoratedObject()->getText();
    }

    /**
     * @getExcerpt
     */
    public function getExcerpt($maxLength = 255)
    {
        return StringUtil::encodeHTML($this->getDecoratedObject()->getText());
    }

    /**
     * @__toString
     */
    public function __toString()
    {
        return $this->getMessage();
    }

    /**
     * @getComments
     */
    public function getComments()
    {
        return '';
    }

    /**
     * @getComments
     */
    public function getUsername()
    {
        $this->getDecoratedObject()->username;
    }

    /**
     * @getCategories
     */
    public function getCategories()
    {
        return [];
    }

    /**
     * @getTime
     */
    public function getTime()
    {
        return $this->getDecoratedObject()->getTime();
    }

    /**
     * @isVisible
     */
    public function isVisible()
    {
        return $this->getDecoratedObject()->canSee();
    }

    /**
     * @getEnclosure
     */
    public function getEnclosure()
    {
        if ($this->enclosure === null) {
            $url = $this->getDecoratedObject()->getPreviewImage();
            $path = $this->getDecoratedObject()->getPreviewImagePath();
            $this->enclosure = new FeedEnclosure($url, FileUtil::getMimeType($path), \filesize($path));
        }

        return $this->enclosure;
    }
}
