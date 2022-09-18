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
namespace wcf\acp\form;

use wcf\data\package\PackageCache;
use wcf\data\quiz\Quiz;
use wcf\data\quiz\QuizAction;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\QuizBestRatedBoxCacheBuilder;
use wcf\system\cache\builder\QuizBestSolverBoxCacheBuilder;
use wcf\system\cache\builder\QuizFavoriteBoxCacheBuilder;
use wcf\system\cache\builder\QuizNewestBoxCacheBuilder;
use wcf\system\cache\builder\QuizTopSolverBoxCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Shows the Quiz edit form.
 */
class QuizEditForm extends QuizAddForm
{
    public $deleteImage = 0;

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.quiz';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.community.canManageQuiz'];

    /**
     * quiz id
     */
    public $quizID = 0;

    /**
     * quiz object
     */
    public $quiz;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->quizID = \intval($_REQUEST['id']);
        }
        $this->quiz = new Quiz($this->quizID);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }

        if (!$this->quiz->canEdit()) {
            throw new PermissionDeniedException();
        }

        $questionIDs = $this->quiz->getQuestionIDs();
        if (empty($questionIDs)) {
            throw new IllegalLinkException();
        }

        $this->questionIDs = \implode("\n", $questionIDs);

        parent::readParameters();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->text = 'wcf.acp.quiz.text' . $this->quiz->quizID;
        if (I18nHandler::getInstance()->isPlainValue('text')) {
            I18nHandler::getInstance()->remove($this->text);
            $this->text = I18nHandler::getInstance()->getValue('text');
        } else {
            I18nHandler::getInstance()->save('text', $this->text, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->title = 'wcf.acp.quiz.title' . $this->quiz->quizID;
        if (I18nHandler::getInstance()->isPlainValue('title')) {
            I18nHandler::getInstance()->remove($this->title);
            $this->title = I18nHandler::getInstance()->getValue('title');
        } else {
            I18nHandler::getInstance()->save('title', $this->title, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $periodEnd = $this->periodEndObj ? $this->periodEndObj->getTimestamp() : 0;
        $periodStart = $this->periodStartObj ? $this->periodStartObj->getTimestamp() : 0;

        $questionIDs = \explode("\n", StringUtil::unifyNewlines(StringUtil::trim($this->questionIDs)));

        // remove existing image if desired
        if ($this->deleteImage) {
            $image = '';
            @\unlink(WCF_DIR . 'images/quiz/' . $this->quiz->image);
        } else {
            $image = $this->quiz->image;
        }

        // update quiz
        $this->objectAction = new QuizAction([$this->quizID], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'text' => $this->text,
                'title' => $this->title,
                'isActive' => $this->isActive,
                'showBest' => $this->showBest,
                'showComment' => $this->showComment,
                'showCorrect' => $this->showCorrect,
                'showResult' => $this->showResult,
                'showResultButton' => $this->showResultButton,
                'showStats' => $this->showStats,
                'playAgain' => $this->playAgain,
                'hasPeriod' => $this->hasPeriod,
                'periodEnd' => $periodEnd,
                'periodStart' => $periodStart,
                'timeLimit' => $this->timeLimit,
                'points' => $this->points,
                'questions' => \count($questionIDs),
                'image' => $image,
                'showOrder' => $this->showOrder,
                'paused' => $this->paused,
                'randomize' => $this->randomize,
                'assignResult' => $this->assignResult,
                'assignGroupIDs' => \serialize($this->assignGroupIDs),
                'groupIDs' => \serialize([]),
            ]),
            'tmpHash' => $this->tmpHash,
        ]);
        $this->objectAction->executeAction();

        // save quiz_to_question
        $sql = "DELETE FROM    wcf" . WCF_N . "_quiz_to_question
                WHERE        quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID]);

        WCF::getDB()->beginTransaction();
        $sql = "INSERT INTO    wcf" . WCF_N . "_quiz_to_question
                    (quizID, questionID)
                VALUES        (?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        foreach ($questionIDs as $id) {
            $statement->execute([$this->quiz->quizID, $id]);
        }
        WCF::getDB()->commitTransaction();

        // transform conditions array into one-dimensional array
        $conditions = [];
        foreach ($this->conditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }

        ConditionHandler::getInstance()->updateConditions($this->quiz->quizID, $this->quiz->getConditions(), $conditions);

        $this->saved();

        // reload object to update preview image
        $this->quiz = new Quiz($this->quiz->quizID);

        // update caches
        QuizBestRatedBoxCacheBuilder::getInstance()->reset();
        QuizBestSolverBoxCacheBuilder::getInstance()->reset();
        QuizFavoriteBoxCacheBuilder::getInstance()->reset();
        QuizNewestBoxCacheBuilder::getInstance()->reset();
        QuizTopSolverBoxCacheBuilder::getInstance()->reset();

        // show success
        WCF::getTPL()->assign([
            'success' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            I18nHandler::getInstance()->setOptions('text', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->quiz->text, 'wcf.acp.quiz.text\d+');
            $this->text = $this->quiz->text;

            I18nHandler::getInstance()->setOptions('title', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->quiz->title, 'wcf.acp.quiz.title\d+');
            $this->title = $this->quiz->title;

            $this->hasPeriod = $this->quiz->hasPeriod;
            $this->isActive = $this->quiz->isActive;
            $this->showResult = $this->quiz->showResult;
            $this->showResultButton = $this->quiz->showResultButton;
            $this->showCorrect = $this->quiz->showCorrect;
            $this->showComment = $this->quiz->showComment;
            $this->showBest = $this->quiz->showBest;
            $this->showStats = $this->quiz->showStats;
            $this->playAgain = $this->quiz->playAgain;

            $dateTime = DateUtil::getDateTimeByTimestamp($this->quiz->periodEnd);
            $dateTime->setTimezone(WCF::getUser()->getTimeZone());
            $this->periodEnd = $dateTime->format('c');
            $dateTime = DateUtil::getDateTimeByTimestamp($this->quiz->periodStart);
            $dateTime->setTimezone(WCF::getUser()->getTimeZone());
            $this->periodStart = $dateTime->format('c');
            $this->timeLimit = $this->quiz->timeLimit;
            $this->points = $this->quiz->points;
            $this->showOrder = $this->quiz->showOrder;
            $this->paused = $this->quiz->paused;
            $this->randomize = $this->quiz->randomize;

            $this->assignGroupIDs = \unserialize($this->quiz->assignGroupIDs);
            $this->assignResult = $this->quiz->assignResult;

            // conditions
            $conditions = $this->quiz->getConditions();
            foreach ($conditions as $condition) {
                $this->conditions[$condition->getObjectType()->conditiongroup][$condition->objectTypeID]->getProcessor()->setData($condition);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'quiz' => $this->quiz,
            'action' => 'edit',
        ]);
    }
}
