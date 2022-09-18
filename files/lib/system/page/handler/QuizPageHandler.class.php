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
namespace wcf\system\page\handler;

use wcf\data\quiz\QuizList;
use wcf\data\quiz\QuizResultList;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Page handler for Quiz.
 */
class QuizPageHandler extends AbstractMenuPageHandler
{
    /**
     * number of unread quizzes
     */
    protected static $unreadQuizzes;

    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null)
    {
        if (self::$unreadQuizzes === null) {
            self::$unreadQuizzes = 0;

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField('unplayedQuizzes');

                // cache does not exist or is outdated
                if ($data === null) {
                    // get played quizzes
                    $playedIDs = [];
                    $resultList = new QuizResultList();
                    $resultList->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
                    $resultList->readObjects();
                    $playeds = $resultList->getObjects();
                    foreach ($playeds as $played) {
                        $playedIDs[] = $played->quizID;
                    }
                    $playedIDs = \array_unique($playedIDs);

                    // check mark all as read
                    $objectTypeID = VisitTracker::getInstance()->getObjectTypeID('com.uz.wcf.quiz');
                    $time = 0;
                    $sql = "SELECT    visitTime
                            FROM    wcf" . WCF_N . "_tracked_visit_type
                            WHERE    objectTypeId = ? AND userID = ?";
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute([$objectTypeID, WCF::getUser()->userID]);
                    if ($row = $statement->fetchArray()) {
                        $time = $row['visitTime'];
                    }

                    // get quizzes not played
                    $quizList = new QuizList();
                    if (!empty($playedIDs)) {
                        $quizList->getConditionBuilder()->add('quizID NOT IN (?)', [$playedIDs]);
                    }
                    if ($time) {
                        $quizList->getConditionBuilder()->add('time > ?', [$time]);
                    }
                    $quizList->readObjects();
                    $quizzes = $quizList->getObjects();
                    foreach ($quizzes as $quiz) {
                        if ($quiz->canPlay()) {
                            self::$unreadQuizzes++;
                        }
                    }

                    // update storage data
                    UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'unplayedQuizzes', self::$unreadQuizzes);
                } else {
                    self::$unreadQuizzes = $data;
                }
            }
        }

        return self::$unreadQuizzes;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        return WCF::getSession()->getPermission('user.quiz.canSee') ? true : false;
    }
}
