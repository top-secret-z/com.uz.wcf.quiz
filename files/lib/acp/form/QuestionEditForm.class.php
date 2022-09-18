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
use wcf\data\quiz\question\Question;
use wcf\data\quiz\question\QuestionAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the question edit form.
 */
class QuestionEditForm extends QuestionAddForm
{
    /**
     * question id
     */
    public $questionID = 0;

    /**
     * question object
     */
    public $questionObj;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->questionID = \intval($_REQUEST['id']);
        }
        $this->questionObj = new Question($this->questionID);
        if (!$this->questionObj->questionID) {
            throw new IllegalLinkException();
        }

        if (!$this->questionObj->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->question = 'wcf.acp.quiz.question' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('question')) {
            I18nHandler::getInstance()->remove($this->question);
            $this->question = I18nHandler::getInstance()->getValue('question');
        } else {
            I18nHandler::getInstance()->save('question', $this->question, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->answerOne = 'wcf.acp.quiz.answerOne' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('answerOne')) {
            I18nHandler::getInstance()->remove($this->answerOne);
            $this->answerOne = I18nHandler::getInstance()->getValue('answerOne');
        } else {
            I18nHandler::getInstance()->save('answerOne', $this->answerOne, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->answerTwo = 'wcf.acp.quiz.answerTwo' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('answerTwo')) {
            I18nHandler::getInstance()->remove($this->answerTwo);
            $this->answerTwo = I18nHandler::getInstance()->getValue('answerTwo');
        } else {
            I18nHandler::getInstance()->save('answerTwo', $this->answerTwo, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->answerThree = 'wcf.acp.quiz.answerThree' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('answerThree')) {
            I18nHandler::getInstance()->remove($this->answerThree);
            $this->answerThree = I18nHandler::getInstance()->getValue('answerThree');
        } else {
            I18nHandler::getInstance()->save('answerThree', $this->answerThree, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->answerFour = 'wcf.acp.quiz.answerFour' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('answerFour')) {
            I18nHandler::getInstance()->remove($this->answerFour);
            $this->answerFour = I18nHandler::getInstance()->getValue('answerFour');
        } else {
            I18nHandler::getInstance()->save('answerFour', $this->answerFour, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->answerFive = 'wcf.acp.quiz.answerFive' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('answerFive')) {
            I18nHandler::getInstance()->remove($this->answerFive);
            $this->answerFive = I18nHandler::getInstance()->getValue('answerFive');
        } else {
            I18nHandler::getInstance()->save('answerFive', $this->answerFive, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->answerSix = 'wcf.acp.quiz.answerSix' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('answerSix')) {
            I18nHandler::getInstance()->remove($this->answerSix);
            $this->answerSix = I18nHandler::getInstance()->getValue('answerSix');
        } else {
            I18nHandler::getInstance()->save('answerSix', $this->answerSix, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        $this->comment = 'wcf.acp.quiz.comment' . $this->questionObj->questionID;
        if (I18nHandler::getInstance()->isPlainValue('comment')) {
            I18nHandler::getInstance()->remove($this->comment);
            $this->comment = I18nHandler::getInstance()->getValue('comment');
        } else {
            I18nHandler::getInstance()->save('comment', $this->comment, 'wcf.acp.quiz', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'));
        }

        // update question
        // add edit note on edit of user question
        $editedBy = '';
        if (!$this->questionObj->isACP) {
            $editedBy = WCF::getUser()->username;
        }

        // remove existing image if desired
        if ($this->deleteImage) {
            $image = '';
            @\unlink(WCF_DIR . 'images/quiz/question/' . $this->questionObj->image);
        } else {
            $image = $this->questionObj->image;
        }

        $this->objectAction = new QuestionAction([$this->questionID], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'question' => $this->question,
                'answerOne' => $this->answerOne,
                'answerTwo' => $this->answerTwo,
                'answerThree' => $this->answerThree,
                'answerFour' => $this->answerFour,
                'answerFive' => $this->answerFive,
                'answerSix' => $this->answerSix,
                'comment' => $this->comment,
                'correct' => $this->correct,
                'count' => $this->count,
                'editedBy' => $editedBy,
                'image' => $image,
                'categoryID' => $this->categoryID ? $this->categoryID : null,
            ]),
        ]);
        $this->objectAction->executeAction();

        $this->saved();

        // reload object to update preview image
        $this->questionObj = new Question($this->questionObj->questionID);

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
            I18nHandler::getInstance()->setOptions('question', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->question, 'wcf.acp.quiz.question\d+');
            $this->question = $this->questionObj->question;

            I18nHandler::getInstance()->setOptions('answerOne', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->answerOne, 'wcf.acp.quiz.answerOne\d+');
            $this->answerOne = $this->questionObj->answerOne;
            I18nHandler::getInstance()->setOptions('answerTwo', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->answerTwo, 'wcf.acp.quiz.answerTwo\d+');
            $this->answerTwo = $this->questionObj->answerTwo;
            I18nHandler::getInstance()->setOptions('answerThree', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->answerThree, 'wcf.acp.quiz.answerThree\d+');
            $this->answerThree = $this->questionObj->answerThree;
            I18nHandler::getInstance()->setOptions('answerFour', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->answerFour, 'wcf.acp.quiz.answerFour\d+');
            $this->answerFour = $this->questionObj->answerFour;
            I18nHandler::getInstance()->setOptions('answerFive', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->answerFive, 'wcf.acp.quiz.answerFive\d+');
            $this->answerFive = $this->questionObj->answerFive;
            I18nHandler::getInstance()->setOptions('answerSix', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->answerSix, 'wcf.acp.quiz.answerSix\d+');
            $this->answerSix = $this->questionObj->answerSix;

            I18nHandler::getInstance()->setOptions('comment', PackageCache::getInstance()->getPackageID('com.uz.wcf.quiz'), $this->questionObj->comment, 'wcf.acp.quiz.comment\d+');
            $this->comment = $this->questionObj->comment;
            $this->correct = $this->questionObj->correct;
            $this->categoryID = $this->questionObj->categoryID;
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
            'questionObj' => $this->questionObj,
            'action' => 'edit',
        ]);
    }
}
