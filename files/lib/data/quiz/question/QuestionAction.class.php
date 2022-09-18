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

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\IUploadAction;
use wcf\data\quiz\QuizList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\user\notification\object\QuizUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

use const PHP_INT_MAX;

/**
 * Executes Question related actions.
 */
class QuestionAction extends AbstractDatabaseObjectAction implements IToggleAction, IUploadAction
{
    /**
     * image limits 4:3
     */
    const PREVIEW_IMAGE_MAX_HEIGHT = 600;

    const PREVIEW_IMAGE_MAX_WIDTH = 800;

    /**
     * @inheritDoc
     */
    protected $className = QuestionEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.community.canManageQuiz'];

    protected $permissionsDelete = ['admin.community.canManageQuiz', 'user.quiz.canSubmitQuestions'];

    protected $permissionsUpdate = ['admin.community.canManageQuiz'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'update'];

    /**
     * question
     */
    protected $question;

    /**
     * @inheritDoc
     */
    public function create()
    {
        $question = parent::create();

        // handle question image
        $this->updatePreviewImage($question);

        return $question;
    }

    /**
     * Validates update action.
     */
    public function validateUpdate()
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $question) {
            if (!$question->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        // tbd
        //QuestionCacheBuilder::getInstance()->reset();
    }

    /**
     * Validates the delete action.
     */
    public function validateDelete()
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        // not used by quiz, permission
        foreach ($this->getObjects() as $question) {
            if (!$question->canEdit()) {
                throw new PermissionDeniedException();
            }
            if ($question->isUsedByQuiz()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // delete notifications
        foreach ($this->getObjects() as $question) {
            if (UserNotificationHandler::getInstance()->getObjectTypeID('com.uz.wcf.quiz.user.notification')) {
                UserNotificationHandler::getInstance()->removeNotifications('com.uz.wcf.quiz.user.notification', [$question->questionID]);
            }
        }

        parent::delete();
    }

    /**
     * Validates the getQuizFromQuestion action.
     */
    public function validateGetQuizListFromQuestion()
    {
        $this->question = new Question($this->parameters['questionID']);
        if (!$this->question->questionID) {
            throw new IllegalLinkException();
        }

        if (!WCF::getSession()->getPermission('user.quiz.canSee')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * getQuizFromQuestion action.
     */
    public function getQuizListFromQuestion()
    {
        $quizIDs = [];
        $sql = "SELECT    quizID
                FROM    wcf" . WCF_N . "_quiz_to_question
                WHERE    questionID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->question->questionID]);
        while ($row = $statement->fetchArray()) {
            $quizIDs[] = $row['quizID'];
        }

        if (\count($quizIDs)) {
            $quizList = new QuizList();
            $quizList->sqlOrderBy = 'quizID';
            $quizList->getConditionBuilder()->add('quizID IN(?)', [$quizIDs]);
            $quizList->readObjects();

            WCF::getTPL()->assign([
                'quizes' => $quizList->getObjects(),
                'question' => $this->question,
            ]);

            return [
                'template' => WCF::getTPL()->fetch('quizListQuizzesDialog'),
            ];
        }

        return '';
    }

    /**
     * Validates the upload action.
     * copied from
     * @author    Alexander Ebert
     * @copyright    2001-2015 WoltLab GmbH
     * @license    GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @package    com.woltlab.wcf
     * @subpackage    data.style
     * @category    Community Framework
     */
    public function validateUpload()
    {
        // check upload permissions
        if (!WCF::getSession()->getPermission('admin.community.canManageQuiz') && !WCF::getSession()->getPermission('user.quiz.canSubmitImage')) {
            throw new PermissionDeniedException();
        }

        $this->readString('tmpHash');
        $this->readInteger('questionID', true);

        if ($this->parameters['questionID']) {
            $this->question = new Question($this->parameters['questionID']);
            if ($this->question === null || !$this->question->questionID) {
                throw new UserInputException('questionID');
            }
        }

        $uploadHandler = $this->parameters['__files'];
        if (\count($uploadHandler->getFiles()) != 1) {
            throw new IllegalLinkException();
        }

        // check max filesize, allowed file extensions etc.
        $uploadHandler->validateFiles(new DefaultUploadFileValidationStrategy(PHP_INT_MAX, ['jpg', 'jpeg', 'png', 'gif', 'svg']));
    }

    /**
     * Handles uploaded question images.
     *
     * @return    array<string>
     */
    public function upload()
    {
        // save files
        $files = $this->parameters['__files']->getFiles();
        $file = $files[0];

        try {
            if (!$file->getValidationErrorType()) {
                // shrink preview image if necessary
                $fileLocation = $file->getLocation();

                $imageData = \getimagesize($fileLocation);
                if ($imageData[0] > self::PREVIEW_IMAGE_MAX_WIDTH || $imageData[1] > self::PREVIEW_IMAGE_MAX_HEIGHT) {
                    try {
                        $adapter = ImageHandler::getInstance()->getAdapter();
                        $adapter->loadFile($fileLocation);
                        $fileLocation = FileUtil::getTemporaryFilename();
                        $thumbnail = $adapter->createThumbnail(self::PREVIEW_IMAGE_MAX_WIDTH, self::PREVIEW_IMAGE_MAX_HEIGHT, false);
                        $adapter->writeImage($thumbnail, $fileLocation);
                        $imageData = \getimagesize($fileLocation);
                    } catch (SystemException $e) {
                        throw new UserInputException('image');
                    }
                }

                // move uploaded file
                if (@\copy($fileLocation, WCF_DIR . 'images/quiz/question/question-' . $this->parameters['tmpHash'] . '.' . $file->getFileExtension())) {
                    @\unlink($fileLocation);

                    // store extension within session variables
                    WCF::getSession()->register('question-' . $this->parameters['tmpHash'], $file->getFileExtension());

                    if ($this->parameters['questionID']) {
                        $this->updatePreviewImage($this->question);

                        return [
                            'url' => WCF::getPath() . 'images/quiz/question/question-' . $this->parameters['questionID'] . '.' . $file->getFileExtension(),
                        ];
                    }

                    // return result
                    return [
                        'url' => WCF::getPath() . 'images/quiz/question/question-' . $this->parameters['tmpHash'] . '.' . $file->getFileExtension(),
                    ];
                } else {
                    throw new UserInputException('image', 'uploadFailed');
                }
            }
        } catch (UserInputException $e) {
            $file->setValidationErrorType($e->getType());
        }

        return ['errorType' => $file->getValidationErrorType()];
    }

    /**
     * Updates question image.
     */
    protected function updatePreviewImage(Question $question)
    {
        if (!isset($this->parameters['tmpHash'])) {
            return;
        }

        $fileExtension = WCF::getSession()->getVar('question-' . $this->parameters['tmpHash']);
        if ($fileExtension !== null) {
            $oldFilename = WCF_DIR . 'images/quiz/question/question-' . $this->parameters['tmpHash'] . '.' . $fileExtension;
            if (\file_exists($oldFilename)) {
                $filename = 'question-' . $question->questionID . '.' . $fileExtension;
                if (@\rename($oldFilename, WCF_DIR . 'images/quiz/question/' . $filename)) {
                    // delete old file if it has a different file extension
                    if ($question->image != $filename) {
                        @\unlink(WCF_DIR . 'images/quiz/question/' . $question->image);

                        // update filename in database
                        $sql = "UPDATE    wcf" . WCF_N . "_quiz_question
                                SET        image = ?
                                WHERE    questionID = ?";
                        $statement = WCF::getDB()->prepareStatement($sql);
                        $statement->execute([
                            $filename,
                            $question->questionID,
                        ]);
                    }
                } else {
                    // remove temp file
                    @\unlink($oldFilename);
                }
            }
        }
    }

    /**
     * Validates the getRandomQuestions action.
     */
    public function validateGetRandomQuestions()
    {
        if (!WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Executes the getRandomQuestions action.
     */
    public function getRandomQuestions()
    {
        $list = new QuestionList();
        $list->getConditionBuilder()->add('approved = 1');

        if (QUIZ_CATEGORY_ON) {
            if (!isset($this->parameters['categoryIDs'])) {
                return ['text' => WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.question.categoryID.error.notSelected')];
            }

            $ids = $this->parameters['categoryIDs'];
            if (\in_array(0, $ids)) {
                $list->getConditionBuilder()->add('categoryID IS NULL OR categoryID IN (?)', [$ids]);
            } else {
                $list->getConditionBuilder()->add('categoryID IN (?)', [$ids]);
            }
        }

        $list->readObjectIDs();
        $ids = $list->getObjectIDs();

        if (!\count($ids)) {
            return ['text' => WCF::getLanguage()->get('wcf.acp.quiz.question.categoryID.error.notFound')];
        }

        \shuffle($ids);
        $ids = \array_slice($ids, 0, $this->parameters['count']);

        return ['text' => \implode("\n", $ids)];
    }

    /**
     * Validates the getQuestionList action.
     */
    public function validateGetQuestionList()
    {
        if (!WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Executes the getQuestionList action.
     */
    public function getQuestionList()
    {
        $questions = $questionIDs = [];

        if (isset($this->parameters['idText'])) {
            $questionIDs = \explode("\n", StringUtil::unifyNewlines(StringUtil::trim($this->parameters['idText'])));

            // get questions, take any value for questionID
            $list = new QuestionList();
            $list->getConditionBuilder()->add('questionID IN(?)', [$questionIDs]);
            $list->readObjects();
            $questionList = $list->getObjects();

            $unknown = WCF::getLanguage()->get('wcf.acp.quiz.question.unknown');
            $empty = WCF::getLanguage()->get('wcf.acp.quiz.question.empty');

            foreach ($questionIDs as $id) {
                if (isset($questionList[$id])) {
                    $questions[] = [
                        'id' => \mb_strlen($id) > 5 ? \substr($id, 0, 3) . '...' : $id,
                        'question' => WCF::getLanguage()->get($questionList[$id]->question),
                    ];
                } else {
                    $questions[] = [
                        'id' => \mb_strlen($id) > 5 ? \substr($id, 0, 3) . '...' : $id,
                        'question' => $unknown,
                    ];
                }
            }
        }

        WCF::getTPL()->assign([
            'questions' => $questions,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('quizListQuestionsDialog'),
        ];
    }

    /**
     * Validates the getAnswerListFromQuestion action.
     */
    public function validateGetAnswerListFromQuestion()
    {
        $this->question = new Question($this->parameters['questionID']);
        if (!$this->question->questionID) {
            throw new IllegalLinkException();
        }

        if (!$this->question->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * getAnswerListFromQuestion action.
     */
    public function getAnswerListFromQuestion()
    {
        // get questions in all languages
        $questions = [];
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $temp = [
                'language' => $language->languageName,
                'question' => $language->get($this->question->question),
                'answerOne' => $language->get($this->question->answerOne),
                'answerTwo' => $language->get($this->question->answerTwo),
                'answerThree' => $language->get($this->question->answerThree),
                'answerFour' => $language->get($this->question->answerFour),
                'answerFive' => $language->get($this->question->answerFive),
                'answerSix' => $language->get($this->question->answerSix),
                'correct' => $this->question->correct,
                'count' => $this->question->count,
            ];
            $questions[] = $temp;
        }

        WCF::getTPL()->assign([
            'questions' => $questions,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('quizListAnswersDialog'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $question) {
            if (!$question->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        foreach ($this->getObjects() as $question) {
            $question->update([
                'approved' => $question->approved ? 0 : 1,
            ]);

            // set notification
            if (!$question->isACP && !$question->approved) {
                $questObj = new Question($question->questionID);
                UserNotificationHandler::getInstance()->fireEvent('uzQuizQuestionOk', 'com.uz.wcf.quiz.user.notification', new QuizUserNotificationObject($questObj), [$questObj->userID]);
            } else {
                // use remove although question is not deleted. There is only one user per question...
                if (UserNotificationHandler::getInstance()->getObjectTypeID('com.uz.wcf.quiz.user.notification')) {
                    UserNotificationHandler::getInstance()->removeNotifications('com.uz.wcf.quiz.user.notification', [$question->questionID]);
                }
            }
        }
    }
}
