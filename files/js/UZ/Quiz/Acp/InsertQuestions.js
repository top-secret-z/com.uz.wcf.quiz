/**
 * Auto inserts questions into the quiz add form
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
define(['Ajax'], function(Ajax) {
	"use strict";
	
	function UZQuizAcpInsertQuestions() { this.init(); }
	
	UZQuizAcpInsertQuestions.prototype = {
		init: function() {
			this._categoryOn = ~~elById('quiz_category_on').value;
			
			var button = elBySel('.jsInsertQuestionsButton');
			button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
		},
		
		_click: function(event) {
			event.preventDefault();
			
			var $categoryIDs = []
			
			if (this._categoryOn) {
				var $categories = document.getElementsByName('categoryIDs');
				for (var i = 0; i < $categories.length; i++) {
					if ($categories[i].checked) {
						$categoryIDs.push(parseInt($categories[i].value));
					}
				}
			}
			
			Ajax.api(this, {
				actionName:	'getRandomQuestions',
				parameters:	{
					count: 		~~elById('insertField').value,
					categoryIDs: $categoryIDs
				}
			});
		},
		
		_ajaxSuccess: function(data) {
			elById('questionIDs').value = data.returnValues.text;
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\quiz\\question\\QuestionAction'
				}
			};
		}
	};
	
	return UZQuizAcpInsertQuestions;
});
