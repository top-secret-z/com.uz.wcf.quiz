/**
 * Handles uploading a quiz image.
 * 
 * @author        2016-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.wcf.quiz
 */
define(['Core', 'Dom/Traverse', 'Language', 'Ui/Notification', 'Upload'], function(Core, DomTraverse, Language, UiNotification, Upload) {
    "use strict";

    function UZQuizAcpUploadQuizImage(quizID, tmpHash) {
        this._quizID = ~~quizID;
        this._tmpHash = tmpHash;

        Upload.call(this, 'uploadImage', 'quizImage', {
            className: 'wcf\\data\\quiz\\QuizAction'
        });
    }
    Core.inherit(UZQuizAcpUploadQuizImage, Upload, {
        /**
         * @see    WoltLabSuite/Core/Upload#_createFileElement
         */
        _createFileElement: function(file) {
            return this._target;
        },

        /**
         * @see    WoltLabSuite/Core/Upload#_getParameters
         */
        _getParameters: function() {
            return {
                quizID: this._quizID,
                tmpHash: this._tmpHash
            };
        },

        /**
         * @see    WoltLabSuite/Core/Upload#_success
         */
        _success: function(uploadId, data) {
            var error = DomTraverse.childByClass(this._button.parentNode, 'innerError');
            if (data.returnValues.url) {
                elAttr(this._target, 'src', data.returnValues.url + '?timestamp=' + Date.now());

                if (error) {
                    elRemove(error);
                }

                UiNotification.show();
            }
            else if (data.returnValues.errorType) {
                if (!error) {
                    error = elCreate('small');
                    error.className = 'innerError';

                    this._button.parentNode.appendChild(error);
                }

                error.textContent = Language.get('wcf.acp.quiz.quiz.image.error.' + data.returnValues.errorType);
            }
        }
    });

    return UZQuizAcpUploadQuizImage;
});
