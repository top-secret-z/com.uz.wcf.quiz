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
namespace wcf\data\quiz\question;

use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a Question.
 */
class Question extends DatabaseObject implements IRouteController
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'quiz_question';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'questionID';

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->question;
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('QuizQuestionList', [
            'object' => $this,
            'questionID' => $this->questionID,
            'forceFrontend' => true,
        ], '#question' . $this->questionID);
    }

    /**
     * get question as array in user's language
     */
    public function getQuestion()
    {
        $answers = [];
        $answers[] = WCF::getLanguage()->get($this->question->answerOne);
        $answers[] = WCF::getLanguage()->get($this->question->answerTwo);
        if (!empty($this->question->answerThree)) {
            $answers[] = WCF::getLanguage()->get($this->question->answerThree);
        }
        if (!empty($this->question->answerFour)) {
            $answers[] = WCF::getLanguage()->get($this->question->answerFour);
        }
        if (!empty($this->question->answerFive)) {
            $answers[] = WCF::getLanguage()->get($this->question->answerFive);
        }
        if (!empty($this->question->answerSix)) {
            $answers[] = WCF::getLanguage()->get($this->question->answerSix);
        }

        return [
            'question' => WCF::getLanguage()->get($this->question),
            'correct' => $this->question->correct,
            'answers' => $answers,
        ];
    }

    /**
     * returns 0 if not used by a quiz
     */
    public function isUsedByQuiz()
    {
        $sql = "SELECT    COUNT(*) AS count
                FROM    wcf" . WCF_N . "_quiz_to_question
                WHERE    questionID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->questionID]);
        $row = $statement->fetchArray();

        return $row['count'];
    }

    /**
     * Returns the preview image path.
     */
    public function getPreviewImage()
    {
        if ($this->image && \file_exists(WCF_DIR . 'images/quiz/question/' . $this->image)) {
            return WCF::getPath() . 'images/quiz/question/' . $this->image;
        }

        return '';
    }

    /**
     * Returns true if the current user can edit this question.
     */
    public function canEdit()
    {
        if (WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
            return true;
        }

        if (!$this->isACP && $this->approved) {
            return false;
        }

        if (WCF::getSession()->getPermission('user.quiz.canSubmitQuestions') && $this->userID == WCF::getUser()->userID) {
            return true;
        }

        return false;
    }
}
