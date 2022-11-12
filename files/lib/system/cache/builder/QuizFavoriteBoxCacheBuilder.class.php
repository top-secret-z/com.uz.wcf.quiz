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
namespace wcf\system\cache\builder;

use wcf\data\quiz\QuizList;

/**
 * Caches the favorite quizzes.
 */
class QuizFavoriteBoxCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 600;

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        if (!MODULE_UZQUIZ) {
            return [];
        }

        $quizList = new QuizList();
        $quizList->getConditionBuilder()->add("isActive = 1");
        $quizList->getConditionBuilder()->add('(hasPeriod = ? OR (periodStart < ? && periodEnd > ?))', [0, TIME_NOW, TIME_NOW]);
        $quizList->getConditionBuilder()->add("counter > 0");
        $quizList->sqlOrderBy = 'counter DESC';
        $quizList->sqlLimit = 100;
        $quizList->readObjects();

        return $quizList->getObjects();
    }
}
