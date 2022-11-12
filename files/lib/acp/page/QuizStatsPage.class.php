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
namespace wcf\acp\page;

use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizResultList;
use wcf\page\SortablePage;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the quiz stats page
 */
class QuizStatsPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.quiz.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.community.canManageQuiz'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 25;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'resultID';

    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['resultID', 'time', 'username', 'result'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = QuizResultList::class;

    // data
    public $quiz;

    public $stats = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }

        if (!empty($_REQUEST['id'])) {
            $id = \intval($_REQUEST['id']);
            $this->quiz = new Quiz($id);
            if (!$this->quiz->quizID) {
                throw new IllegalLinkException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function initObjectList()
    {
        parent::initObjectList();

        // quiz
        $this->objectList->getConditionBuilder()->add('quizID = ?', [$this->quiz->quizID]);

        // stats
        $this->stats = [];
        $questions = $this->quiz->questions;

        // average rate, users
        $sql = "SELECT    COALESCE(SUM(total), 0) AS total, COALESCE(SUM(correct), 0) AS correct, COUNT(DISTINCT userID) AS users
                FROM    wcf" . WCF_N . "_quiz_to_user
                WHERE    quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID]);
        $row = $statement->fetchArray();

        $average = 0;
        if ($row['total']) {
            $average = $row['correct'] / $row['total'] * 100;
        }
        $users = $row['users'];

        $language = WCF::getLanguage();

        $this->stats['questions'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.questions', ['value' => $questions]);
        $this->stats['users'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.users', ['value' => $users]);
        $this->stats['average'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.average', ['value' => $average]);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $objects = $this->objectList->getObjects();
        if (\count($objects)) {
            $userIDs = [];
            foreach ($objects as $result) {
                $result->user = UserProfileRuntimeCache::getInstance()->getObject($result->userID);
            }
        }

        WCF::getTPL()->assign([
            'quiz' => $this->quiz,
            'stats' => $this->stats,
        ]);
    }
}
