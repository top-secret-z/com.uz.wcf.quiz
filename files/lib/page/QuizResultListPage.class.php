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
namespace wcf\page;

use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizResultList;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Shows the user's quiz result page.
 */
class QuizResultListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'com.uz.wcf.quiz.QuizResultListPage';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_UZQUIZ'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.quiz.canPlay'];

    /**
     * @inheritDoc
     */
    public $enableTracking = true;

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 15;

    /**
     * @inheritDoc
     */
    public $objectListClassName = QuizResultList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['time', 'quizTitle', 'result'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * result stats
     */
    public $stats = [];

    public $userID = 0;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        //     add breadcrumbs
        if (MODULE_UZQUIZ) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.wcf.quiz.QuizPage');
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->userID = WCF::getUser()->userID;

        $this->objectList->getConditionBuilder()->add("userID = ?", [$this->userID]);

        // stats
        $this->stats = $this->objectList->getStats($this->userID);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'userID' => $this->userID,
            'stats' => $this->stats,
        ]);
    }
}
