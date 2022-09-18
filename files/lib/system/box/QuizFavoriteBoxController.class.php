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
namespace wcf\system\box;

use wcf\data\quiz\QuizViewableFavoriteList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows most favorite quizzes.
 */
class QuizFavoriteBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    public $defaultLimit = 5;

    public $maximumLimit = 15;

    public $minimumLimit = 1;

    /**
     * @inheritDoc
     */
    public $validSortFields = [];

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = '';

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Quiz');
    }

    /**
     * @inheritDoc
     */
    public function hasLink()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        return new QuizViewableFavoriteList();
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->fetch('boxQuizFavorite', 'wcf', [
            'quizzes' => $this->objectList,
        ]);
    }
}
