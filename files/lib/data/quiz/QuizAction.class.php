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

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\data\IToggleAction;
use wcf\data\IUploadAction;
use wcf\data\quiz\question\Question;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupList;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileList;
use wcf\system\cache\builder\QuizBestRatedBoxCacheBuilder;
use wcf\system\cache\builder\QuizBestSolverBoxCacheBuilder;
use wcf\system\cache\builder\QuizFavoriteBoxCacheBuilder;
use wcf\system\cache\builder\QuizNewestBoxCacheBuilder;
use wcf\system\cache\builder\QuizTopSolverBoxCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

use const PHP_INT_MAX;

/**
 * Executes Quiz related actions.
 */
class QuizAction extends AbstractDatabaseObjectAction implements IToggleAction, IGroupedUserListAction, IUploadAction
{
    /**
     * image limits
     */
    const PREVIEW_IMAGE_MAX_HEIGHT = 128;

    const PREVIEW_IMAGE_MAX_WIDTH = 204;

    /**
     * @inheritDoc
     */
    protected $className = QuizEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.community.canManageQuiz'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.community.canManageQuiz'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.community.canManageQuiz'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update'];

    /**
     * quiz data
     */
    protected $quiz;

    protected $question;

    protected $type = '';

    protected $correctDb = 0;

    protected $uniqueID = '';

    /**
     * @inheritDoc
     */
    public function create()
    {
        $quiz = parent::create();

        // handle quiz image
        $this->updatePreviewImage($quiz);

        return $quiz;
    }

    /**
     * Validates update action.
     */
    public function validateUpdate()
    {
        if (!WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        // reset user storage and cache
        UserStorageHandler::getInstance()->resetAll('unplayedQuizzes');
        $this->resetCache();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // delete activity points
        $userToItems = $objectIDs = [];
        foreach ($this->objects as $quiz) {
            // get all users who participated
            $sql = "SELECT        userID, COUNT(*) AS count
                    FROM        wcf" . WCF_N . "_quiz_to_user
                    WHERE        quizID = ?
                    GROUP BY    userID";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$quiz->quizID]);
            while ($row = $statement->fetchArray()) {
                $userToItems[$row['userID']] = $row['count'];
                $objectIDs[] = $quiz->quizID;
            }

            // remove activity points
            if (\count($userToItems)) {
                UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.quiz.activityPointEvent.quiz', $userToItems);
            }

            // conditions
            ConditionHandler::getInstance()->deleteConditions('com.uz.wcf.quiz.condition', [$quiz->quizID]);
        }

        // remove activity events
        if (\count($objectIDs) && UserActivityEventHandler::getInstance()->getObjectTypeID('com.uz.wcf.quiz.recentActivityEvent.quiz')) {
            UserActivityEventHandler::getInstance()->removeEvents('com.uz.wcf.quiz.recentActivityEvent.quiz', $objectIDs);
        }
        // rating
        if (\count($objectIDs) && UserActivityEventHandler::getInstance()->getObjectTypeID('com.uz.wcf.quiz.recentActivityEvent.quizRating')) {
            UserActivityEventHandler::getInstance()->removeEvents('com.uz.wcf.quiz.recentActivityEvent.quizRating', $objectIDs);
        }

        // reset user storage and cache
        UserStorageHandler::getInstance()->resetAll('unplayedQuizzes');
        $this->resetCache();

        parent::delete();
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        parent::validateUpdate();
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        foreach ($this->objects as $quiz) {
            $quiz->update([
                'isActive' => $quiz->isActive ? 0 : 1,
            ]);
        }
        // reset user storage and cache
        UserStorageHandler::getInstance()->resetAll('unplayedQuizzes');
        $this->resetCache();
    }

    /**
     * Validates the getQuiz action.
     */
    public function validateGetQuiz()
    {
        if (!isset($this->parameters['quizID'])) {
            throw new PermissionDeniedException();
        }
        $this->quiz = new Quiz($this->parameters['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }

        if (!$this->quiz->canPlay()) {
            throw new PermissionDeniedException();
        }
        if ($this->quiz->mustPause()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Get the quiz data
     */
    public function getQuiz()
    {
        $questions = $temp = [];
        $from = WCF::getLanguage()->get('wcf.user.quiz.question.from');
        $questionIDs = $this->quiz->getQuestionIDs();

        if ($this->quiz->randomize) {
            \shuffle($questionIDs);
        }

        foreach ($questionIDs as $id) {
            $question = new Question($id);

            $answers = [];
            $answers[] = StringUtil::encodeHTML(WCF::getLanguage()->get($question->answerOne));
            $answers[] = StringUtil::encodeHTML(WCF::getLanguage()->get($question->answerTwo));
            if (!empty($question->answerThree)) {
                $answers[] = StringUtil::encodeHTML(WCF::getLanguage()->get($question->answerThree));
            }
            if (!empty($question->answerFour)) {
                $answers[] = StringUtil::encodeHTML(WCF::getLanguage()->get($question->answerFour));
            }
            if (!empty($question->answerFive)) {
                $answers[] = StringUtil::encodeHTML(WCF::getLanguage()->get($question->answerFive));
            }
            if (!empty($question->answerSix)) {
                $answers[] = StringUtil::encodeHTML(WCF::getLanguage()->get($question->answerSix));
            }

            $max = 0;
            foreach ($answers as $answer) {
                $temp = \explode(' ', $answer);
                $maxThis = \max(\array_map('strlen', $temp));
                if ($max < $maxThis) {
                    $max = $maxThis;
                }
            }

            if ($this->quiz->showComment) {
                $comment = StringUtil::encodeHTML(WCF::getLanguage()->get($question->comment));
            }

            $author = '';
            if (!$question->isACP) {
                $author = '(' . $from . ' ' . $question->username . ')';
            }

            $questions[] = [
                'question' => StringUtil::encodeHTML(WCF::getLanguage()->get($question->question)),
                'id' => $question->questionID,
                'author' => $author,
                'answers' => $answers,
                'correct' => 0,
                'comment' => $this->quiz->showComment ? $comment : '',
                'image' => $question->getPreviewImage(),
                'maxLen' => $max * 10,
            ];
        }

        // unique id
        $uniqueID = WCF::getUser()->userID . $this->quiz->quizID . \uniqid('', true);
        $sql = "INSERT INTO    wcf" . WCF_N . "_quiz_temp
                    (uniqueID, time, quizID, correct)
            VALUES        (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$uniqueID, TIME_NOW, $this->quiz->quizID, 0]);

        $data = [
            'text' => $this->quiz->getText(),
            'title' => $this->quiz->getTranslatedTitle(),
            'showBest' => $this->quiz->showBest,
            'showComment' => $this->quiz->showComment,
            'showCorrect' => $this->quiz->showCorrect,
            'showResult' => $this->quiz->showResult,
            'showStats' => $this->quiz->showStats,
            'questions' => $questions,
            'timeLimit' => $this->quiz->timeLimit,
            'afterQuiz' => '',
            'beforeQuiz' => '',
            'uniqueID' => $uniqueID,
        ];

        $parameters = [
            'quiz' => $this->quiz,
            'data' => $data,
        ];

        EventHandler::getInstance()->fireAction($this, 'getBeforeQuiz', $parameters);
        $data = $parameters['data'];
        EventHandler::getInstance()->fireAction($this, 'getAfterQuiz', $parameters);

        return $parameters['data'];
    }

    /**
     * Validates the saveResult action.
     */
    public function validateSaveResult()
    {
        // check quiz
        if (!isset($this->parameters['quizID'])) {
            throw new IllegalLinkException();
        }
        $this->quiz = new Quiz($this->parameters['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }

        if (!isset($this->parameters['uniqueID'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }

        // get correct answers and check vs uniqueID
        $sql = "SELECT    quizID, correct
                FROM    wcf" . WCF_N . "_quiz_temp
                WHERE    uniqueID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->parameters['uniqueID']]);
        $row = $statement->fetchSingleRow();
        if (!$row) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
        $this->correctDb = $row['correct'];
        $this->uniqueID = $this->parameters['uniqueID'];

        // parameters vs configuration
        if (!isset($this->parameters['showCorrect']) || !isset($this->parameters['showResult'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
        if ($this->quiz->showCorrect != $this->parameters['showCorrect'] || $this->quiz->showResult != $this->parameters['showResult']) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }

        // check answers plausible
        if ($this->quiz->showCorrect || $this->quiz->showResult) {
            if ($this->correctDb != $this->parameters['correctAnswers']) {
                throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
            }
        }

        // changed question count
        if (!isset($this->parameters['questionCount'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
        if ($this->quiz->getQuestionCount() != $this->parameters['questionCount']) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }

        // play only once / time lock
        $last = $this->quiz->getLastSolved(WCF::getUser()->userID);
        if (!$this->quiz->playAgain && $last) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }

        if ($this->quiz->paused && $last) {
            if ($last + $this->quiz->paused * 60 > TIME_NOW) {
                throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
            }
        }
    }

    /**
     * Save the quiz result
     */
    public function saveResult()
    {
        // update quiz counter
        $editor = new QuizEditor($this->quiz);
        $editor->updateCounters(['counter' => 1]);

        $user = WCF::getUser();

        // correct answers
        $total = $this->quiz->getQuestionCount();
        $correct = $this->parameters['correctAnswers'];

        // use value from db if required
        if (!$this->quiz->showCorrect && !$this->quiz->showResult) {
            $correct = $this->correctDb;
        }

        // just in case...
        if ($correct > $total) {
            $correct = $total;
        }

        // cleanup db
        $sql = "DELETE FROM    wcf" . WCF_N . "_quiz_temp
                WHERE        uniqueID = ? AND quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->uniqueID, $this->quiz->quizID]);

        // allow multiple user entries
        $sql = "INSERT INTO    wcf" . WCF_N . "_quiz_to_user
                    (quizID, userID, total, correct, time)
                VALUES        (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID, $user->userID, $total, $correct, TIME_NOW]);

        // update user count, rating and played
        $sql = "SELECT    COALESCE(SUM(total), 0) AS total,
                        COALESCE(SUM(correct), 0) AS correct
                FROM        wcf" . WCF_N . "_quiz_to_user
                WHERE        userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$user->userID]);
        $row = $statement->fetchArray();
        $rate = $row['correct'] / $row['total'] * 100;

        $played = [];
        if (!empty($user->uzQuizPlayed)) {
            $played = \explode(',', $user->uzQuizPlayed);
        }
        $played[] = $this->quiz->quizID;
        $played = \array_unique($played);

        $editor = new UserEditor($user);
        $editor->update([
            'uzQuiz' => $user->uzQuiz + 1,
            'uzQuizRate' => $rate,
            'uzQuizPlayed' => \implode(',', $played),
        ]);

        // group assignment
        if (QUIZ_GROUP_ON) {
            $assignGroupIDs = $this->getAllowedGroupIDs($this->quiz->assignGroupIDs);
            if (\count($assignGroupIDs)) {
                // user already in group?
                $userGroupIDs = $user->getGroupIDs();
                $assign = false;
                foreach ($assignGroupIDs as $id) {
                    if (!\in_array($id, $userGroupIDs)) {
                        $assign = true;
                        break;
                    }
                }
                if ($assign) {
                    // check result
                    $result = \ceil($correct / $this->quiz->questions * 100);
                    if ($result >= $this->quiz->assignResult) {
                        // assign to groups
                        $action = new UserAction([$user->userID], 'addToGroups', [
                            'groups' => $assignGroupIDs,
                            'addDefaultGroups' => false,
                            'deleteOldGroups' => false,
                        ]);
                        $action->executeAction();

                        // add comment
                        $userGroupList = new UserGroupList();
                        $userGroupList->getConditionBuilder()->add('user_group.groupID IN (?)', [$assignGroupIDs]);
                        $userGroupList->readObjects();
                        $groupNames = [];
                        foreach ($userGroupList as $userGroup) {
                            $groupNames[] = $userGroup->getName();
                        }
                        $value = \implode(', ', $groupNames);
                        $comment = [
                            'languageItem' => 'wcf.user.quiz.comment.addedGroups',
                            'value' => $value,
                        ];
                        $sql = "INSERT INTO    wcf" . WCF_N . "_quiz_to_user_comment
                                        (quizID, userID, comment, displayOnce, time)
                                VALUES        (?, ?, ?, ?, ?)";
                        $statement = WCF::getDB()->prepareStatement($sql);
                        $statement->execute([$this->quiz->quizID, $user->userID, \serialize($comment), 1, TIME_NOW]);
                    } else {
                        // add comment not successfull
                        $comment = [
                            'languageItem' => 'wcf.user.quiz.comment.notAddedGroups',
                            'value' => $this->quiz->showCorrect ? $this->quiz->assignResult : 0,
                        ];
                        $sql = "INSERT INTO    wcf" . WCF_N . "_quiz_to_user_comment
                                        (quizID, userID, comment, displayOnce, time)
                                    VALUES        (?, ?, ?, ?, ?)";
                        $statement = WCF::getDB()->prepareStatement($sql);
                        $statement->execute([$this->quiz->quizID, $user->userID, \serialize($comment), 1, TIME_NOW]);
                    }
                }
            }
        }

        // result list
        $sql = "INSERT INTO    wcf" . WCF_N . "_quiz_result
                    (quizID, quizTitle, userID, username, result, time)
                VALUES        (?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID, $this->quiz->title, $user->userID, $user->username, $correct / $total * 100, TIME_NOW]);

        // activity points and event
        UserActivityPointHandler::getInstance()->fireEvent('com.uz.wcf.quiz.activityPointEvent.quiz', $this->quiz->quizID, $user->userID);
        if (MODULE_UZQUIZ_ACTIVITY) {
            UserActivityEventHandler::getInstance()->fireEvent('com.uz.wcf.quiz.recentActivityEvent.quiz', $this->quiz->quizID);
        }

        // user storage and cache
        UserStorageHandler::getInstance()->reset([$user->userID], 'unplayedQuizzes');
        $this->resetCache();

        // build result and comment text
        if ($this->quiz->showCorrect) {
            $result = WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.result.detail', [
                'correct' => $correct,
                'total' => $total,
            ]);
            $points = \intval($correct / $total * 100);
            if ($points < 11) {
                $comment = WCF::getLanguage()->get('wcf.user.quiz.comment.zeroToTen');
            } elseif ($points < 36) {
                $comment = WCF::getLanguage()->get('wcf.user.quiz.comment.tenToThirtyfive');
            } elseif ($points < 66) {
                $comment = WCF::getLanguage()->get('wcf.user.quiz.comment.thirtyfiveToSixtyfive');
            } elseif ($points < 91) {
                $comment = WCF::getLanguage()->get('wcf.user.quiz.comment.sixtyfiveToNinety');
            } elseif ($points < 100) {
                $comment = WCF::getLanguage()->get('wcf.user.quiz.comment.ninetyToNinetynine');
            } else {
                $comment = WCF::getLanguage()->get('wcf.user.quiz.comment.hundert');
            }
        } else {
            $result = WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.result.detail.off');
            $comment = '';
        }

        // further result action
        if (isset($this->parameters['userSelect'])) {
            $parameters = [
                'quiz' => $this->quiz,
                'userID' => $user->userID,
                'correct' => $correct,
                'total' => $total,
                'selected' => $this->parameters['userSelect'],
            ];
            EventHandler::getInstance()->fireAction($this, 'quizLogSaveResult', $parameters);
        }

        // paused
        $paused = '';
        if ($this->quiz->playAgain && $this->quiz->paused) {
            $hours = \floor($this->quiz->paused / 60);
            $minutes = ($this->quiz->paused % 60);
            $paused = WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.paused', ['hours' => $hours, 'minutes' => $minutes]);
        }

        return [
            'result' => $result,
            'comment' => $comment,
            'paused' => $paused,
        ];
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
        if (!WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
            throw new PermissionDeniedException();
        }

        $this->readString('tmpHash');
        $this->readInteger('quizID', true);

        if ($this->parameters['quizID']) {
            $this->quiz = new Quiz($this->parameters['quizID']);
            if ($this->quiz === null || !$this->quiz->quizID) {
                throw new UserInputException('quizID');
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
     * Handles uploaded quiz images.
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
                if (@\copy($fileLocation, WCF_DIR . 'images/quiz/preview-' . $this->parameters['tmpHash'] . '.' . $file->getFileExtension())) {
                    @\unlink($fileLocation);

                    // store extension within session variables
                    WCF::getSession()->register('preview-' . $this->parameters['tmpHash'], $file->getFileExtension());

                    if ($this->parameters['quizID']) {
                        $this->updatePreviewImage($this->quiz);

                        return [
                            'url' => WCF::getPath() . 'images/quiz/preview-' . $this->parameters['quizID'] . '.' . $file->getFileExtension(),
                        ];
                    }

                    // return result
                    return [
                        'url' => WCF::getPath() . 'images/quiz/preview-' . $this->parameters['tmpHash'] . '.' . $file->getFileExtension(),
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
     * Updates preview image.
     */
    protected function updatePreviewImage(Quiz $quiz)
    {
        if (!isset($this->parameters['tmpHash'])) {
            return;
        }

        $fileExtension = WCF::getSession()->getVar('preview-' . $this->parameters['tmpHash']);
        if ($fileExtension !== null) {
            $oldFilename = WCF_DIR . 'images/quiz/preview-' . $this->parameters['tmpHash'] . '.' . $fileExtension;
            if (\file_exists($oldFilename)) {
                $filename = 'preview-' . $quiz->quizID . '.' . $fileExtension;
                if (@\rename($oldFilename, WCF_DIR . 'images/quiz/' . $filename)) {
                    // delete old file if it has a different file extension
                    if ($quiz->image != $filename) {
                        @\unlink(WCF_DIR . 'images/quiz/' . $quiz->image);

                        // update filename in database
                        $sql = "UPDATE    wcf" . WCF_N . "_quiz
                                SET        image = ?
                                WHERE    quizID = ?";
                        $statement = WCF::getDB()->prepareStatement($sql);
                        $statement->execute([
                            $filename,
                            $quiz->quizID,
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
     * Validates getGroupedUserList action.
     */
    public function validateGetGroupedUserList()
    {
        if (!isset($this->parameters['data']['quizID'])) {
            throw new PermissionDeniedException();
        }
        $this->quiz = new Quiz($this->parameters['data']['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }
        $this->type = $this->parameters['data']['type'];

        if ($this->type == 'best' && !$this->quiz->showBest) {
            throw new PermissionDeniedException();
        }
        if ($this->type == 'stat' && !$this->quiz->showStats) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Executes the getGroupedUserList action.
     */
    public function getGroupedUserList()
    {
        // average rate
        $sql = "SELECT    COALESCE(SUM(total), 0) AS total,
                        COALESCE(SUM(correct), 0) AS correct
                FROM    wcf" . WCF_N . "_quiz_to_user
                WHERE    quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID]);
        $row = $statement->fetchArray();
        $rate = $row['correct'] / $row['total'] * 100;

        if ($this->type == 'best') {
            // 20 best solves, latest first
            $tops = $temp = $userIDs = [];
            if (QUIZ_BEST_ONCE) {
                $sql = "SELECT        userID, MAX(result) AS best, MAX(time) AS latest
                        FROM        wcf" . WCF_N . "_quiz_result
                        WHERE        quizID = ?
                        GROUP BY    userID
                        ORDER BY    best DESC, latest DESC";
            } else {
                $sql = "SELECT        userID, result AS best, time AS latest
                        FROM        wcf" . WCF_N . "_quiz_result
                        WHERE        quizID = ?
                        ORDER BY    best DESC, latest DESC";
            }
            $statement = WCF::getDB()->prepareStatement($sql, 20);
            $statement->execute([$this->quiz->quizID]);
            while ($row = $statement->fetchArray()) {
                $temp[] = [
                    'userID' => $row['userID'],
                    'rate' => $row['best'],
                    'time' => $row['latest'],
                ];
                $userIDs[] = $row['userID'];
            }
        } else {
            // 20 latest solves, latest first
            $tops = $temp = $userIDs = [];
            $sql = "SELECT        userID, total, correct, time
                    FROM        wcf" . WCF_N . "_quiz_to_user
                    WHERE        quizID = ?
                    ORDER BY    time DESC, fakeID DESC";
            $statement = WCF::getDB()->prepareStatement($sql, 20);
            $statement->execute([$this->quiz->quizID]);
            while ($row = $statement->fetchArray()) {
                $temp[] = [
                    'userID' => $row['userID'],
                    'rate' => $row['correct'] / $row['total'] * 100,
                    'time' => $row['time'],
                ];
                $userIDs[] = $row['userID'];
            }
        }

        // read all relevant profiles at once
        $userList = new UserProfileList();
        $userList->getConditionBuilder()->add("user_table.userID IN (?)", [$userIDs]);
        $userList->readObjects();
        $list = $userList->getObjects();

        // build data for template
        foreach ($temp as $combi) {
            $tops[] = [
                'user' => $list[$combi['userID']],
                'rate' => $combi['rate'],
                'time' => $combi['time'],
            ];
        }
        $userList = null;
        $temp = null;

        WCF::getTPL()->assign([
            'count' => 0,
            'quiz' => $this->quiz,
            'userList' => $tops,
            'rate' => $rate,
            'type' => $this->type,
        ]);

        return [
            'pageCount' => 1,
            'template' => WCF::getTPL()->fetch('quizTopUserList'),
        ];
    }

    /**
     * Validates GetStats action.
     */
    public function validateGetStats()
    {
        if (!isset($this->parameters['quizID'])) {
            throw new PermissionDeniedException();
        }
        $this->quiz = new Quiz($this->parameters['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }

        if (!WCF::getSession()->getPermission('admin.community.canManageQuiz')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Executes the getStats action.
     */
    public function getStats()
    {
        $data = [];
        $total = $this->quiz->questions;

        // average rate, users
        $sql = "SELECT    COALESCE(SUM(total), 0) AS total, COALESCE(SUM(correct), 0) AS correct, COUNT(DISTINCT userID) AS users
                FROM    wcf" . WCF_N . "_quiz_to_user
                WHERE    quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID]);
        $row = $statement->fetchArray();

        $averageRate = 0;
        if ($row['total']) {
            $averageRate = $row['correct'] / $row['total'] * 100;
        }
        $userCount = $row['users'];

        // best/worst solves
        $sql = "SELECT    MAX(correct) AS correctMax, MIN(correct) AS correctMin
                FROM    wcf" . WCF_N . "_quiz_to_user
                WHERE    quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID]);
        $row = $statement->fetchArray();

        $correctMin = $correctMax = $total;
        $maxRate = $minRate = 0;
        if ($row['correctMax'] <= $total) {
            $correctMax = $row['correctMax'];
        }
        if ($row['correctMin'] <= $total) {
            $correctMin = $row['correctMin'];
        }
        if ($total > 0) {
            $maxRate = $correctMax / $total * 100;
            $minRate = $correctMin / $total * 100;
        }

        // top user
        $topUsers = [];
        $sql = "SELECT        userID, COUNT(*) AS count
                FROM        wcf" . WCF_N . "_quiz_to_user
                WHERE        quizID = ?
                GROUP BY    userID
                ORDER BY     count DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 10);
        $statement->execute([$this->quiz->quizID]);
        while ($row = $statement->fetchArray()) {
            $user = UserProfileRuntimeCache::getInstance()->getObject($row['userID']);
            $topUsers[$user->username] = $row['count'];
        }

        $language = WCF::getLanguage();

        $data['title'] = $this->quiz->getTranslatedTitle();
        $data['counter'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.counter', ['value' => $this->quiz->counter]);
        $data['userCount'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.userCount', ['value' => $userCount]);
        $data['averageRate'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.averageRate', ['value' => $averageRate]);
        $data['maxRate'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.maxRate', ['value' => $maxRate]);
        $data['minRate'] = WCF::getLanguage()->getDynamicVariable('wcf.acp.quiz.stats.minRate', ['value' => $minRate]);
        $data['topUserTitle'] = WCF::getLanguage()->get('wcf.acp.quiz.stats.topUserTitle');
        $data['tops'] = $topUsers;

        WCF::getTPL()->assign([
            'data' => $data,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('quizShowStatsDialog'),
        ];
    }

    /**
     * Validates the mark as read action.
     */
    public function validateMarkAllAsRead()
    {
        // does nothing
    }

    /**
     * Marks all quizzes as played to remove badge in menu tab.
     */
    public function markAllAsRead()
    {
        VisitTracker::getInstance()->trackTypeVisit('com.uz.wcf.quiz');

        // reset storage and notifications
        if (WCF::getUser()->userID) {
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unplayedQuizzes');
        }
    }

    /**
     * Validates the getComments action.
     */
    public function validateGetComments()
    {
        if (!isset($this->parameters['quizID'])) {
            throw new PermissionDeniedException();
        }
        $this->quiz = new Quiz($this->parameters['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Validates the checkQuizResult action.
     */
    public function validateCheckQuizResult()
    {
        if (!isset($this->parameters['quizID'])) {
            throw new PermissionDeniedException();
        }
        $this->quiz = new Quiz($this->parameters['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }

        // allow quiz result dialog
        if (!$this->quiz->showResultButton) {
            throw new PermissionDeniedException();
        }

        // not during quiz
        if (!isset($this->parameters['current']) || !isset($this->parameters['uniqueID'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
        if (\intval($this->parameters['current']) != $this->quiz->getQuestionCount()) {
            throw new PermissionDeniedException();
        }
        $sql = "SELECT    COUNT(*)
                FROM    wcf" . WCF_N . "_quiz_temp
                WHERE    uniqueID = ? AND quizID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->parameters['uniqueID'], $this->quiz->quizID]);
        if ($statement->fetchSingleColumn()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * checkQuizResult does nothing
     */
    public function checkQuizResult()
    {
    }

    /**
     * Get comments relating to quiz / user.
     */
    public function getComments()
    {
        $comments = [];
        $sql = "SELECT        comment
                FROM        wcf" . WCF_N . "_quiz_to_user_comment
                WHERE        quizID = ? AND userID = ? AND displayed = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->quiz->quizID, WCF::getUser()->userID, 0]);
        while ($row = $statement->fetchArray()) {
            $comment = \unserialize($row['comment']);
            $comments[] = WCF::getLanguage()->getDynamicVariable($comment['languageItem'], ['value' => $comment['value']]);
        }
        // reset to displayed if once
        if (\count($comments)) {
            $sql = "UPDATE    wcf" . WCF_N . "_quiz_to_user_comment
                    SET        displayed = ?
                    WHERE    quizID = ? AND userID = ? AND displayed = ? AND displayOnce = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([1, $this->quiz->quizID, WCF::getUser()->userID, 0, 1]);
        }

        return [
            'comments' => $comments,
            'showResultButton' => $this->quiz->showResultButton,
        ];
    }

    /**
     * reset quiz caches
     */
    public function resetCache()
    {
        QuizBestRatedBoxCacheBuilder::getInstance()->reset();
        QuizBestSolverBoxCacheBuilder::getInstance()->reset();
        QuizFavoriteBoxCacheBuilder::getInstance()->reset();
        QuizNewestBoxCacheBuilder::getInstance()->reset();
        QuizTopSolverBoxCacheBuilder::getInstance()->reset();
    }

    /**
     * get allowed groupIDs
     */
    public function getAllowedGroupIDs($groupIDs)
    {
        $groupIDs = \unserialize($groupIDs);
        if (empty($groupIDs)) {
            return [];
        }

        $allowedUserGroupIDs = [];
        foreach (UserGroup::getGroupsByIDs($groupIDs) as $group) {
            if (!$group->isAdminGroup()) {
                $allowedUserGroupIDs[] = $group->groupID;
            }
        }

        return \array_intersect($groupIDs, $allowedUserGroupIDs);
    }

    /**
     * Validates the getCorrect action.
     */
    public function validateGetCorrect()
    {
        // check quiz in general and vs. uniqueID
        if (!isset($this->parameters['quizID']) || !isset($this->parameters['uniqueID'])) {
            throw new IllegalLinkException();
        }
        $this->quiz = new Quiz($this->parameters['quizID']);
        if (!$this->quiz->quizID) {
            throw new IllegalLinkException();
        }

        $sql = "SELECT    quizID
                FROM    wcf" . WCF_N . "_quiz_temp
                WHERE    uniqueID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->parameters['uniqueID']]);
        $quizID = $statement->fetchSingleColumn();
        if ($quizID != $this->quiz->quizID) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }

        // question and selection
        if (!isset($this->parameters['questionID'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
        $this->question = new Question($this->parameters['questionID']);
        if (!$this->question->questionID) {
            throw new IllegalLinkException();
        }
        if (!isset($this->parameters['selected'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }

        // parameters vs configuration
        if (!isset($this->parameters['showCorrect']) || !isset($this->parameters['showResult'])) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
        if ($this->quiz->showCorrect != $this->parameters['showCorrect'] || $this->quiz->showResult != $this->parameters['showResult']) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.quiz.error.parameter'));
        }
    }

    /**
     * gets the correct answer
     */
    public function getCorrect()
    {
        $selected = \intval($this->parameters['selected']) + 1;

        // store in db
        if ($selected == $this->question->correct) {
            $sql = "UPDATE    wcf" . WCF_N . "_quiz_temp
                    SET        correct = correct + 1
                    WHERE    uniqueID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->parameters['uniqueID']]);
        }

        // hide if configured
        if (!$this->quiz->showCorrect && !$this->quiz->showResult) {
            return [
                'correct' => -1,
                'correctAnswer' => -1,
            ];
        }

        // allow correct since either showCorrect or showResult is active
        if ($selected == $this->question->correct) {
            $correct = 1;
        } else {
            $correct = 0;
        }

        if ($this->parameters['showResult']) {
            $correctAnswer = $this->question->correct - 1;
        } else {
            $correctAnswer = -1;
        }

        return [
            'correct' => $correct,
            'correctAnswer' => $correctAnswer,
        ];
    }
}
