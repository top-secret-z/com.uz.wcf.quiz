/**
 * Dialog to display quizzes using the specific question
 * 
 * @author        2016-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.wcf.quiz
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function(Ajax, Language, UiDialog) {
    "use strict";

    function UZQuizAcpListQuizzes() { this.init(); }

    UZQuizAcpListQuizzes.prototype = {
        init: function() {
            var buttons = elBySelAll('.jsListQuizzesButton');
            for (var i = 0, length = buttons.length; i < length; i++) {
                buttons[i].addEventListener(WCF_CLICK_EVENT, this._showDialog.bind(this));
            }
        },

        _showDialog: function(event) {
            event.preventDefault();

            Ajax.api(this, {
                actionName:    'getQuizListFromQuestion',
                parameters:    {
                    questionID:    ~~elData(event.currentTarget, 'object-id')
                }
            });
        },

        _ajaxSuccess: function(data) {
            this._render(data);
        },

        _render: function(data) {
            UiDialog.open(this, data.returnValues.template);
        },

        _ajaxSetup: function() {
            return {
                data: {
                    className: 'wcf\\data\\quiz\\question\\QuestionAction'
                }
            };
        },

        _dialogSetup: function() {
            return {
                id:         'listQuizzes',
                options:     { title: Language.get('wcf.acp.quiz.quizList') },
                source:     null
            };
        }
    };

    return UZQuizAcpListQuizzes;
});
