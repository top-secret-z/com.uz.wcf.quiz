/**
 * Dialog to show stats of a quiz
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.quiz
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function(Ajax, Language, UiDialog) {
	"use strict";
	
	function UZQuizAcpShowStats() { this.init(); }
	
	UZQuizAcpShowStats.prototype = {
		init: function() {
			var buttons = elBySelAll('.jsShowStatsButton');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener(WCF_CLICK_EVENT, this._showDialog.bind(this));
			}
		},
		
		_showDialog: function(event) {
			event.preventDefault();
			
			Ajax.api(this, {
				actionName:	'getStats',
				parameters:	{
					quizID:	~~elData(event.currentTarget, 'object-id')
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
					className: 'wcf\\data\\quiz\\QuizAction'
				}
			};
		},
		
		_dialogSetup: function() {
			return {
				id: 		'ShowStats',
				options: 	{ title: Language.get('wcf.acp.quiz.stats') },
				source: 	null
			};
		}
	};
	
	return UZQuizAcpShowStats;
});
