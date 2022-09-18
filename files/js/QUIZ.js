/**
 * Class and function collection for Quiz
 * 
 * @author        2016-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.wcf.quiz
 */

/**
 * Initialize namespace
 */
if (!QUIZ) var QUIZ = {};

/**
 * Handles a quiz.
 */
QUIZ.Quiz = Class.extend({
    /**
     * action proxy
     * @var    WCF.Action.Proxy
     */
    _proxy: null,

    /**
     * proxy action
     * string
     */
    _actionName: null,

    /**
     * quiz data
     */
    _quizID: 0,
    _quizText: '',
    _quizTitle: '',
    _questions: null,
    _questionCount: 0,
    _currentIndex: 0,
    _correctAnswers: null,
    _showBest: 1,
    _showStats: 1,
    _showCorrect: 0,
    _showResult: 0,
    _showComment: 0,
    _uniqueID: '',
    _beforeQuiz: '',
    _afterQuiz: '',

    // array to hold correct indices
    _userCorrect: null,
    _userIncorrect: null,
    _userSelect: [],

    /**
     * result data
     */
    _resultText: '',
    _resultComment: '',
    _paused: '',

    /**
     * timing data
     */
    _intervalID: 0,
    _timeLimit: 0,
    _dialog: null,
    _displayed: 0,

    /**
     * Initializes the Quiz class.
     */
    init: function() {
        // hide containers
        $('#quizSubmitContainer').hide();
        $('#quizContainer').hide();
        $('#afterQuiz').hide();

        // proxy
        this._actionName = '';
        this._proxy = new WCF.Action.Proxy({
            success: $.proxy(this._success, this)
        });

        // bind event listener
        $('.quizButton').click($.proxy(this._startQuiz, this));
        $('.statsButton').click($.proxy(this._statQuiz, this));
        $('.bestButton').click($.proxy(this._bestQuiz, this));
        $('#quizSaveButton').click($.proxy(this._quizSaveButton, this));
        $('#quizNextButton').click($.proxy(this._quizNextButton, this));
        $('#quizAbortButton').click($.proxy(this._quizAbortButton, this));
        $('#quizStatsButton').click($.proxy(this._quizStatsButton, this));
        $('#quizBestButton').click($.proxy(this._quizBestButton, this));
        $('#quizResultButton').click($.proxy(this._quizResultButton, this));
        $('#quizOverviewButton').click($.proxy(this._quizOverviewButton, this));
    },

    /**
     * handles click on stats button in overwiew
     */
    _statQuiz: function(event) {
        event.preventDefault();
        this._quizID = $(event.currentTarget).data('objectID');

        var $userList = new WCF.User.List('wcf\\data\\quiz\\QuizAction', WCF.Language.get('wcf.user.quiz.stats'), {
            data: {
                quizID: this._quizID,
                type: 'stat'
            }
        });
        $userList.open();
    },

    /**
     * handles click on best button in overwiew
     */
    _bestQuiz: function(event) {
        event.preventDefault();
        this._quizID = $(event.currentTarget).data('objectID');

        var $userList = new WCF.User.List('wcf\\data\\quiz\\QuizAction', WCF.Language.get('wcf.user.quiz.best'), {
            data: {
                quizID: this._quizID,
                type: 'best'
            }
        });
        $userList.open();
    },

    /**
     * handles click on result button
     */
    _quizResultButton: function(event) {
        // check allowed
        this._actionName = 'checkQuizResult';
        this._proxy.setOption('data', {
            actionName: 'checkQuizResult',
            className: 'wcf\\data\\quiz\\QuizAction',
            parameters: {
                quizID: this._quizID,
                current: this._currentIndex,
                uniqueID: this._uniqueID
            }
        });
        this._proxy.sendRequest();
    },

    /**
     * get quiz data and start quiz flow
     */
    _startQuiz: function(event) {
        event.preventDefault();
        this._quizID = $(event.currentTarget).data('objectID');

        // get quiz data
        this._actionName = 'getQuiz';
        this._proxy.setOption('data', {
            actionName: 'getQuiz',
            className: 'wcf\\data\\quiz\\QuizAction',
            parameters: {
                quizID: this._quizID
            }
        });
        this._proxy.sendRequest();

        // warn user when interrupting the started quiz
        $(window).on('beforeunload', function(){
            return WCF.Language.get('wcf.user.quiz.browserAbort');
        });
    },

    /**
     * Handles successful AJAX requests.
     * 
     * @param    object        data
     * @param    string        textStatus
     * @param    jQuery        jqXHR
     */
    _success: function(data, textStatus, jqXHR) {
        switch (this._actionName) {
            case 'getQuiz':
                this._quizTitle = data.returnValues.title;
                this._quizText = data.returnValues.text;
                this._timeLimit = parseInt(data.returnValues.timeLimit);
                this._showBest = parseInt(data.returnValues.showBest);
                this._showComment = parseInt(data.returnValues.showComment);
                this._showCorrect = parseInt(data.returnValues.showCorrect);
                this._showResult = parseInt(data.returnValues.showResult);
                this._showStats = parseInt(data.returnValues.showStats);
                this._uniqueID = data.returnValues.uniqueID;
                this._beforeQuiz = data.returnValues.beforeQuiz;
                this._afterQuiz = data.returnValues.afterQuiz;
                this._questions = data.returnValues.questions;
                this._questionCount = parseInt(this._questions.length);
                this._currentIndex = 0;
                this._correctAnswers = 0;
                this._userCorrect = [];
                this._userIncorrect = [];

                // switch containers, hide timer
                $('#quizListContainer').hide();
                $('#quizContainer').show();
                $('#afterQuiz').show();
                $('#quizTimer').hide();

                // before quiz
                if (this._beforeQuiz.template !== undefined) {
                    $('#beforeQuiz').append(this._beforeQuiz.template);
                }

                // write title and text
                $('#quizTitle').append(this._quizTitle);

                // start timer if time limited
                if (this._timeLimit) {
                    this._displayed = 0;
                    this._intervalID = setInterval($.proxy(this._executeTimer, this), 1000);
                    $('#quizTimer').show();
                }

                // display first question and set end time
                this._displayQuestion();

                // after quiz
                if (this._afterQuiz.template !== undefined) {
                    $('#afterQuiz').append(this._afterQuiz.template);
                }
            break;

            case 'saveResult':
                this._resultText = data.returnValues.result;
                this._resultComment = data.returnValues.comment;
                this._paused = data.returnValues.paused;

                $(window).unbind('beforeunload');
                // show and store result
                this._displayResult();
            break;

            case 'getComments':
                var comments = data.returnValues.comments;
                var showResultButton = parseInt(data.returnValues.showResultButton);

                // use question again ;-)
                $('#question').remove();
                var question = $('<div>', { id: 'question', "class": 'quizQuestion' });
                question.append($('<p class="quizResult">' + this._resultText + '</p>'));
                question.append($('<p>' + this._resultComment + '</p>'));
                if (this._paused) {
                    question.append($('<br><p>' + this._paused + '</p>'));
                }

                // insert additional comments
                if (comments.length > 0) {
                    question.append($('<br>'));
                }
                for (var i = 0; i < comments.length; i++) {
                    question.append($('<p>' + comments[i] + '</p>'));
                }

                $('#quiz').append(question);

                // end of quiz
                $('#quizAbortButton').hide();
                $('#quizNextButton').hide();
                if (showResultButton) {
                    $('#quizResultButton').show();
                }
                $('#quizSaveButton').hide();
                if (this._showStats) {
                    $('#quizStatsButton').show();
                }
                if (this._showBest) {
                    $('#quizBestButton').show();
                }
                $('#quizOverviewButton').show();
                $('#quizSubmitContainer').show();
            break;

            case 'checkQuizResult':
                // build result dialog iaw parameter

                // show all
                if (this._showCorrect && this._showResult) {
                    var correct = $('<div>', { "class": 'section' });
                    var number = 0;
                    correct.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.correct') + '</h2>'));

                    if (this._userCorrect.length) {
                        for (var i = 0; i < this._userCorrect.length; i++) {
                            number = this._userCorrect[i];
                            number ++;
                            correct.append($('<p>' + number + '. ' + this._questions[this._userCorrect[i]].question + '</p>'));
                            var index = this._questions[this._userCorrect[i]].correct;
                            index--;
                            if (this._showCorrect) {
                                correct.append($('<small>' + WCF.Language.get('wcf.user.quiz.correct') + ' ' + this._questions[this._userCorrect[i]].answers[this._questions[this._userCorrect[i]].correct] + '</small>'));
                                correct.append($('<br><br>'));
                            }
                        }
                    }
                    else {
                        correct.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.none') + '</p>'));
                    }

                    var incorrect = $('<div>', { "class": 'section' });
                    incorrect.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.wrong') + '</h2>'));
                    if (this._userIncorrect.length) {
                        for (var i = 0; i < this._userIncorrect.length; i++) {
                            number = this._userIncorrect[i];
                            number ++;
                            incorrect.append($('<p>' + number + '. ' + this._questions[this._userIncorrect[i]].question + '</p>'));
                            var index = this._questions[this._userIncorrect[i]].correct;
                            index--;
                            if (this._showCorrect) {
                                incorrect.append($('<small>' + WCF.Language.get('wcf.user.quiz.correct') + ' ' + this._questions[this._userIncorrect[i]].answers[this._questions[this._userIncorrect[i]].correct] + '</small>'));
                                incorrect.append($('<br><br>'));
                            }
                        }
                    }
                    else {
                        incorrect.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.none') + '</p>'));
                    }

                    this._dialog = $('<div>').appendTo(document.body);
                    this._dialog.append(correct);
                    this._dialog.append(incorrect);
                    this._dialog.wcfDialog({ title: WCF.Language.get('wcf.user.quiz.result.your') });
                }

                // show only correct
                else if (this._showCorrect) {
                    var correct = $('<div>', { "class": 'section' });
                    var number = 0;
                    correct.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.correct') + '</h2>'));

                    if (this._correctAnswers == 1) {
                        correct.append($('<p>' + this._correctAnswers + ' ' + WCF.Language.get('wcf.user.quiz.question') + '</p>'));
                    }
                    else if (this._correctAnswers > 1) {
                        correct.append($('<p>' + this._correctAnswers + ' ' + WCF.Language.get('wcf.user.quiz.questions') + '</p>'));
                    }
                    else {
                        correct.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.none') + '</p>'));
                    }

                    var incorrect = $('<div>', { "class": 'section' });
                    incorrect.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.wrong') + '</h2>'));

                    var count = this._questions.length - this._correctAnswers;

                    if (count == 1) {
                        incorrect.append($('<p>' + count + ' ' + WCF.Language.get('wcf.user.quiz.question') + '</p>'));
                    }
                    else if (count > 1) {
                        incorrect.append($('<p>' + count + ' ' + WCF.Language.get('wcf.user.quiz.questions') + '</p>'));
                    }
                    else {
                        incorrect.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.none') + '</p>'));
                    }

                    this._dialog = $('<div>').appendTo(document.body);
                    this._dialog.append(correct);
                    this._dialog.append(incorrect);
                    this._dialog.wcfDialog({ title: WCF.Language.get('wcf.user.quiz.result.your') });
                }

                // show only result
                else if (this._showResult) {
                    var number = 0;
                    var correct = $('<div>', { "class": 'section' });
                    correct.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.correct') + '</h2>'));
                    correct.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.hide') + '</p>'));

                    var incorrect = $('<div>', { "class": 'section' });
                    incorrect.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.wrong') + '</h2>'));
                    incorrect.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.hide') + '</p>'));

                    var result = $('<div>', { "class": 'section' });
                    result.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.result') + '</h2>'));

                    for (var i = 0; i < this._questions.length; i++) {
                        number = i + 1
                        result.append($('<p>' + number + '. ' + this._questions[i].question + '</p>'));
                        result.append($('<small>' + WCF.Language.get('wcf.user.quiz.correct') + ' ' + this._questions[i].answers[this._questions[i].correct] + '</small>'));
                        result.append($('<br><br>'));
                    }

                    this._dialog = $('<div>').appendTo(document.body);
                    this._dialog.append(correct);
                    this._dialog.append(incorrect);
                    this._dialog.append(result);
                    this._dialog.wcfDialog({ title: WCF.Language.get('wcf.user.quiz.result.your') });
                }

                // show nothing
                else {
                    var correct = $('<div>', { "class": 'section' });
                    correct.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.correct') + '</h2>'));
                    correct.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.hide') + '</p>'));

                    var incorrect = $('<div>', { "class": 'section' });
                    incorrect.append($('<h2 class="sectionTitle">' + WCF.Language.get('wcf.user.quiz.resultDialog.wrong') + '</h2>'));
                    incorrect.append($('<p>' + WCF.Language.get('wcf.user.quiz.resultDialog.hide') + '</p>'));

                    this._dialog = $('<div>').appendTo(document.body);
                    this._dialog.append(correct);
                    this._dialog.append(incorrect);
                    this._dialog.wcfDialog({ title: WCF.Language.get('wcf.user.quiz.result.your') });
                }
            break;

            case 'getCorrect':
                var correct = data.returnValues.correct;
                var correctAnswer = data.returnValues.correctAnswer;

                // rebuild question as result
                $('#question').remove();
                var question = $('<div>', { id: 'question', "class": 'quizQuestion' });
                question.append($('<p class="quizResultQuestion">' + this._questions[this._currentIndex]['question'] + '</p>'));

                // showCorrect: -1 = hide, 0 = wrong, 1 = correct
                if (correct < 0) {
                    question.append($('<p class="quizResultCorrect">' + WCF.Language.get('wcf.user.quiz.result.saved') + '</p>'));
                }
                else if (correct == 0) {
                    if (this._showCorrect) {
                        question.append($('<p class="quizResultWrong">' + WCF.Language.get('wcf.user.quiz.result.wrong') + '</p>'));
                    }
                    else {
                        question.append($('<p class="quizResultCorrect">' + WCF.Language.get('wcf.user.quiz.result.saved') + '</p>'));
                    }
                }
                else {
                    this._correctAnswers++;
                    if (this._showCorrect) {
                        question.append($('<p class="quizResultCorrect">' + WCF.Language.get('wcf.user.quiz.result.correct') + '</p>'));
                    }
                    else {
                        question.append($('<p class="quizResultCorrect">' + WCF.Language.get('wcf.user.quiz.result.saved') + '</p>'));
                    }
                }

                // showResult: -1 = hide, >= 0 = answer
                if (correctAnswer >= 0) {
                    question.append($('<p>' + WCF.Language.get('wcf.user.quiz.correct') + ' ' + this._questions[this._currentIndex].answers[correctAnswer] + '</p>'));

                    // store answer
                    this._questions[this._currentIndex].correct = correctAnswer;

                    if (correct > 0) {
                        this._userCorrect.push(parseInt(this._currentIndex));
                    }
                    else {
                        this._userIncorrect.push(parseInt(this._currentIndex));
                    }
                }

                if (this._showComment) {
                    if (this._showResult) {
                        question.append($('<br>'));
                    }
                    question.append($('<p>' + this._questions[this._currentIndex].comment + '</p>'));
                }

                $('#quiz').append(question);

                // change buttons
                $('#quizSaveButton').hide();
                $('#quizNextButton').show();
            break;
        }
    },

    /**
     * Executes timer related functions.
     */
    _executeTimer: function() {
        this._timeLimit--;

        // format time and display
        if (this._timeLimit > 0) {
            var num = parseInt(this._timeLimit, 10);
            var hours = Math.floor(num / 3600);
            var minutes = Math.floor((num - (hours * 3600)) / 60);
            var seconds = num - (hours * 3600) - (minutes * 60);
            if (hours < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
            $('#quizTimerValue').text(hours+':'+minutes+':'+seconds);
        }
        else {
            $('#quizTimerValue').text('00:00:00');
        }

        // display expired dialog at 1 sec left for 5 secs, change color of counter
        if (this._timeLimit < 1 && this._displayed == 0) {
            // close existing dialog
            if (this._dialog !== null){
                this._dialog.wcfDialog('close');
            }

            //$('#quizSaveButton').hide();
            //$('#quizNextButton').hide();
            $('#quizTimerValue').addClass('quizQuestionTimerElapsed');
            this._displayed = 1;
            this._dialog = $('<header class="boxHeadline"><strong>' + WCF.Language.get('wcf.user.quiz.timeExpired') + '</strong></header>').wcfDialog({ title: WCF.Language.get('wcf.user.quiz.timeExpired.title'), closeViaModal: false });
        }
        // stop by time
        if (this._timeLimit < -3) {
            this._dialog.wcfDialog('close');
            clearInterval(this._intervalID);

            // write to incorrect array
            for (var i = this._currentIndex; i < this._questionCount; i++) {
                this._userIncorrect.push(parseInt(i));
            }
            this._currentIndex = this._questionCount;
            $('#quizNextButton').click();
        }
    },

    /**
     * get and display question
     */
    _displayQuestion: function() {
        var count = this._currentIndex;
        count++;

        // enable next button again
        $('#quizNextButton').removeAttr('disabled');

        // remove old and build new question
        $('#question').remove();

        // need to break?
        var width = window.screen.width;
        if (width < this._questions[this._currentIndex]['maxLen']){
            var question = $('<div>', { id: 'question', "class": 'quizQuestion quizQuestionBreak' });
        }
        else {
            var question = $('<div>', { id: 'question', "class": 'quizQuestion' });
        }

        question.append($('<p class="quizQuestionCount">' + WCF.Language.get('wcf.user.quiz.question') + ' ' + count + '/' + this._questionCount + ' <small>' + this._questions[this._currentIndex]['author'] + '</small></p>'));
        question.append($('<p class="quizQuestionQuestion">' + this._questions[this._currentIndex]['question'] + '</p>'));

        if (this._questions[this._currentIndex]['image']) {
            question.append($('<p class="quizQuestionImage framed"><img src="' + this._questions[this._currentIndex]['image'] + ' " alt=""></img></p>'));
        }

        for (var i = 0; i < this._questions[this._currentIndex].answers.length; i++) {
            question.append($('<div><span><input id="ans' + i + '" type="radio" name="answer" value=' + i + ' /></span> <label for="ans' + i + '">' + this._questions[this._currentIndex].answers[i] + '</label></div>'));
        }
        $('#quiz').append(question);

        $('#quizAbortButton').show();
        $('#quizNextButton').hide();
        $('#quizResultButton').hide();
        $('#quizSaveButton').show();
        $('#quizStatsButton').hide();
        $('#quizBestButton').hide();
        $('#quizSubmitContainer').show();
        $('#quizOverviewButton').hide();
    },

    /**
     * handle click on save button
     */
    _quizSaveButton: function() {
        // check selected 
        var selected = $('input[name="answer"]:checked').val();
        if (!selected) {
            this._dialog = $('<header class="boxHeadline"><strong>' + WCF.Language.get('wcf.user.quiz.error.noSelection') + '</strong></header>').wcfDialog({ title: WCF.Language.get('wcf.global.error.title') });
            return;
        }

        // stop timer on last question and change color
        if (1 == this._questionCount - this._currentIndex) {
            clearInterval(this._intervalID);
            $('#quizTimerValue').addClass('quizQuestionTimerNotElapsed');
        }

        // user select
        this._userSelect[this._currentIndex] = [this._questions[this._currentIndex]['id'], selected];

        // get correct answer
        this._actionName = 'getCorrect';
        this._proxy.setOption('data', {
            actionName: 'getCorrect',
            className: 'wcf\\data\\quiz\\QuizAction',
            parameters: {
                questionID: this._questions[this._currentIndex]['id'],
                selected: selected,
                showCorrect: this._showCorrect,
                showResult: this._showResult,
                quizID: this._quizID,
                uniqueID: this._uniqueID
            }
        });
        this._proxy.sendRequest();
    },

    /**
     * handle click on next button
     */
    _quizNextButton: function() {
        // disable button to prevent more than one save
        $('#quizNextButton').attr('disabled','disabled');

        // any question left?
        this._currentIndex++;
        if (this._currentIndex < this._questionCount) {
            this._displayQuestion();
        }
        else {
            clearInterval(this._intervalID);
            this._saveResult();
        }
    },

    /**
     * handle click on abort button
     */
    _quizAbortButton: function() {
        WCF.System.Confirmation.show(WCF.Language.get('wcf.user.quiz.abortConfirm'), function (action) {
            if (action === 'confirm') {
                $(window).unbind('beforeunload');
                window.location.reload();
            }
        });
    },

    /**
     * handle click on overview button
     */
    _quizOverviewButton: function() {
        $(window).unbind('beforeunload');
        window.location.reload();
    },

    /**
     * handle click on stats button at the end of a quiz
     */
    _quizStatsButton: function() {
        var $userList = new WCF.User.List('wcf\\data\\quiz\\QuizAction', WCF.Language.get('wcf.user.quiz.stats'), {
            data: {
                quizID: this._quizID,
                type: 'stat'
            }
        });
        $userList.open();
    },

    /**
     * handle click on best button at the end of a quiz
     */
    _quizBestButton: function() {
        var $userList = new WCF.User.List('wcf\\data\\quiz\\QuizAction', WCF.Language.get('wcf.user.quiz.best'), {
            data: {
                quizID: this._quizID,
                type: 'best'
            }
        });
        $userList.open();
    },

    /**
     * save result
     */
    _saveResult: function() {
        this._actionName = 'saveResult';
        this._proxy.setOption('data', {
            actionName: 'saveResult',
            className: 'wcf\\data\\quiz\\QuizAction',
            parameters: {
                quizID: this._quizID,
                showCorrect: this._showCorrect,
                showResult: this._showResult,
                correctAnswers: this._correctAnswers,
                uniqueID: this._uniqueID,
                questionCount: this._questionCount,
                userSelect: this._userSelect
            }
        });
        this._proxy.sendRequest();
    },

    /**
     * display result
     */
    _displayResult: function() {
        this._actionName = 'getComments';
        this._proxy.setOption('data', {
            actionName: 'getComments',
            className: 'wcf\\data\\quiz\\QuizAction',
            parameters: {
                quizID: this._quizID
            }
        });
        this._proxy.sendRequest();
    }
});

/**
 * Marks all quizzes as played.
 */
QUIZ.MarkAllAsRead = Class.extend({
    _proxy: null,

    /**
     * Initializes the class.
     */
    init: function() {
        this._proxy = new WCF.Action.Proxy({
            success: $.proxy(this._success, this)
        });

        // bind event listener
        $('.markAllAsReadButton').click($.proxy(this._click, this));
    },

    /**
     * Handles clicks on the 'mark all as read' button.
     */
    _click: function(event) {
        event.preventDefault();

        this._proxy.setOption('data', {
            actionName: 'markAllAsRead',
            className: 'wcf\\data\\quiz\\QuizAction'
        });

        this._proxy.sendRequest();
    },

    /**
     * Marks all categories as read.
     */
    _success: function(data, textStatus, jqXHR) {
        // remove main menu badge
        $('.mainMenu .active .badge').hide();

        var notify = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
        notify.show();
    }
});

/**
 * Handles quiz rating.
 */
QUIZ.Rating = Class.extend({
    _buttons: { },
    _dialog: null,
    _notification: null,
    _quizID: 0,
    _proxy: null,

    /**
     * Initializes the rating overlay.
     */
    init: function() {
        this._buttons = { };
        this._notification = null;
        this._proxy = new WCF.Action.Proxy({
            success: $.proxy(this._success, this)
        });

        $('.rateButton').click($.proxy(this._click, this));
        $('.ratingDetails').click($.proxy(this._ratingDetails, this));
    },

    /**
     * Handles clicks on a rating button.
     */
    _click: function(event) {
        this._quizID = $(event.currentTarget).data('objectID');

        this._proxy.setOption('data', {
            actionName: 'prepareRating',
            className: 'wcf\\data\\quiz\\rating\\RatingAction',
            parameters: {
                quizID: this._quizID
            }
        });
        this._proxy.sendRequest();
    },

    /**
     * Handles clicks on a rating image.
     */
    _ratingDetails: function(event) {
        this._quizID = $(event.currentTarget).data('objectID');

        var $userList = new WCF.User.List('wcf\\data\\quiz\\rating\\RatingAction', WCF.Language.get('wcf.user.quiz.rating.details'), {
            data: {
                quizID: this._quizID
            }
        });
        $userList.open();
    },

    /**
     * Handles successful AJAX requests.
     */
    _success: function(data, textStatus, jqXHR) {
        this._notification = null;
        if (data.returnValues.rated) {
            var $action = data.returnValues.rated;
            if ($action > 0) {
                this._notification = new WCF.System.Notification(WCF.Language.get('wcf.user.quiz.rating.rate.success'));
            }
            else {
                this._notification = new WCF.System.Notification(WCF.Language.get('wcf.user.quiz.rating.unrate.success'));
            }

            // show success and close dialog
            this._dialog.wcfDialog('close');
            this._notification.show();

            window.location.reload();
        }
        else if (data.returnValues.template) {
            this._showDialog(data.returnValues.template);

            // bind event listener for buttons
            this._dialog.find('.jsSubmitRating').click($.proxy(this._submit, this));
            this._dialog.find('.jsDeleteRating').click($.proxy(this._delete, this));
            this._dialog.find('.jsAbortRating').click($.proxy(this._abort, this));
        }
    },

    /**
     * Displays the dialog overlay.
     */
    _showDialog: function(template) {
        if (this._dialog === null) {
            this._dialog = $('#rating');
            if (!this._dialog.length) {
                this._dialog = $('<div id="rating" />').hide().appendTo(document.body);
            }
        }

        this._dialog.html(template).wcfDialog({
            title: WCF.Language.get('wcf.user.quiz.rating')
        }).wcfDialog('render');
    },

    /**
     * Aborts the rating.
     */
    _abort: function() {
        this._dialog.wcfDialog('close');
    },

    /**
     * Submits the rating.
     */
    _submit: function() {
        var $rating = this._dialog.find('input[type=radio]:checked').val();
        if ($rating !== undefined) { 
            this._proxy.setOption('data', {
                actionName: 'rate',
                className: 'wcf\\data\\quiz\\rating\\RatingAction',
                parameters: {
                    rating: $rating,
                    quizID: this._quizID
                }
            });
            this._proxy.sendRequest();
        }
    },

    /**
     * Deletes the rating.
     */
    _delete: function() {
        this._proxy.setOption('data', {
            actionName: 'unrate',
            className: 'wcf\\data\\quiz\\rating\\RatingAction',
            parameters: {
                quizID: this._quizID
            }
        });
        this._proxy.sendRequest();
    }
});
