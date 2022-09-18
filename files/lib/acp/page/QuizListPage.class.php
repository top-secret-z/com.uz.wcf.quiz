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

use wcf\data\quiz\QuizList;
use wcf\page\SortablePage;

/**
 * Lists the configured Quizes
 */
class QuizListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.quiz.list';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'quizID';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['quizID', 'hasPeriod', 'title', 'counter', 'questions', 'time', 'showOrder'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.community.canManageQuiz'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = QuizList::class;
}
