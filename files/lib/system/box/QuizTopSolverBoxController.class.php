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

use wcf\data\DatabaseObject;
use wcf\system\cache\builder\QuizTopSolverBoxCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows users with most quiz solves.
 */
class QuizTopSolverBoxController extends AbstractDatabaseObjectListBoxController
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
     * users loaded from cache
     */
    public $users = [];

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
        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        if (!MODULE_UZQUIZ || !WCF::getSession()->getPermission('user.quiz.canSee')) {
            return false;
        }

        parent::hasContent();

        return \count($this->users) > 0;
    }

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        $this->readObjects();

        $this->content = $this->getTemplate();
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        EventHandler::getInstance()->fireAction($this, 'readObjects');

        $userIDs = QuizTopSolverBoxCacheBuilder::getInstance()->getData();

        if (!empty($userIDs)) {
            $this->users = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);

            // sort users
            if (!empty($this->users)) {
                DatabaseObject::sort($this->users, 'uzQuiz', 'DESC');
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->fetch('boxQuizTopSolver', 'wcf', [
            'users' => $this->users,
        ]);
    }
}
