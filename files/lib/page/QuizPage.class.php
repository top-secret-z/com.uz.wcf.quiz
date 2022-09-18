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
use wcf\data\quiz\QuizList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the start page of the quiz.
 */
class QuizPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.user.quiz.menu';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_UZQUIZ'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.quiz.canSee'];

    /**
     * @inheritDoc
     */
    public $enableTracking = true;

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 10;

    /**
     * quiz and filter
     */
    public $quiz;

    public $quizID = 0;

    // 0 = all, 1 = unplayed, 2 = played
    public $filter = 0;

    /**
     * @inheritDoc
     */
    public $objectListClassName = QuizList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['time', 'questions', 'counter', 'title', 'timeLimit', 'showOrder', 'ratingTotal'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = QUIZ_DEFAULT_SORT_FIELD;

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = QUIZ_DEFAULT_SORT_ORDER;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['quizID'])) {
            $this->quizID = \intval($_REQUEST['quizID']);
        }
        if ($this->quizID) {
            $this->quiz = new Quiz($this->quizID);
            if (!$this->quiz->quizID) {
                throw new IllegalLinkException();
            }
        }

        // read filter
        if (isset($_REQUEST['filter'])) {
            $this->filter = \intval($_REQUEST['filter']);
            if ($this->filter < 0 || $this->filter > 2) {
                $this->filter = 0;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        // check for permission
        $ids = [];
        $count = 0;
        $ids[] = 0;
        $list = new QuizList();
        $list->readObjects();
        foreach ($list->getObjects() as $quiz) {
            if ($quiz->canSee()) {
                // filter
                if (QUIZ_FILTER && $this->filter > 0) {
                    $solved = $quiz->hasSolved();

                    if ($this->filter == 1 && $solved == true) {
                        if (!QUIZ_FILTER_SUCCESS) {
                            continue;
                        }
                        if ($quiz->getSuccess()) {
                            continue;
                        }
                    }

                    if ($this->filter == 2 && $solved == false) {
                        continue;
                    }
                }
                $ids[] = $quiz->quizID;

                // count quizzes for pageNo; must be younger than selected quiz (sortorder time desc)
                if ($this->quizID) {
                    if ($quiz->time > $this->quiz->time) {
                        $count++;
                    }
                }
            }
        }

        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add("quiz.quizID IN (?)", [$ids]);

        // calculate pageNo, if quizID is set
        if ($this->quizID && $count) {
            $this->pageNo = \intval(\ceil($count / $this->itemsPerPage));
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        // assign filter
        WCF::getTPL()->assign([
            'filter' => $this->filter,
        ]);
    }
}
