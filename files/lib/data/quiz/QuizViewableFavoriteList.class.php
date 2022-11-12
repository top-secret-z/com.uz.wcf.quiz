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

use wcf\system\cache\builder\QuizFavoriteBoxCacheBuilder;

/**
 * Represents a list of most favorite quizzes viewable to user.
 */
class QuizViewableFavoriteList extends QuizList
{
    /**
     * Creates a new list object with max 15 items
     */
    public function __construct()
    {
        parent::__construct();

        // get quiz ids
        $quizList = QuizFavoriteBoxCacheBuilder::getInstance()->getData();
        $quizIDs = [];
        $count = 0;
        foreach ($quizList as $quiz) {
            if ($quiz->canSee()) {
                $quizIDs[] = $quiz->quizID;
                $count++;
                if ($count > 15) {
                    break;
                }
            }
        }

        // get quizzes
        if (!empty($quizIDs)) {
            $this->getConditionBuilder()->add("quizID IN (?)", [$quizIDs]);
            $this->sqlOrderBy = 'counter DESC';
        } else {
            $this->getConditionBuilder()->add("1=0");
        }
    }
}
