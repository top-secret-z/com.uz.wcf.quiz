/**
 * Dialog to display questions of a quiz
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function(Ajax, Language, UiDialog) {
	"use strict";
	
	function UZQuizAcpListQuestions() { this.init(); }
	
	UZQuizAcpListQuestions.prototype = {
		init: function() {
			var button = elBySel('.jsListQuestionsButton');
			button.addEventListener(WCF_CLICK_EVENT, this._showDialog.bind(this));
		},
		
		_showDialog: function(event) {
			event.preventDefault();
			
			Ajax.api(this, {
				actionName:	'getQuestionList',
				parameters:	{
					idText:	elById('questionIDs').value
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
				id: 		'ListQuestions',
				options: 	{ title: Language.get('wcf.acp.quiz.question.list') },
				source: 	null
			};
		}
	};
	
	return UZQuizAcpListQuestions;
});
