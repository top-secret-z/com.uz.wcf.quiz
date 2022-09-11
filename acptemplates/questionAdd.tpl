{include file='header' pageTitle='wcf.acp.quiz.question.'|concat:$action}

<script data-relocate="true">
	require(['Language', 'UZ/Quiz/Acp/UploadQuestionImage'], function(Language, UZQuizAcpUploadQuestionImage) {
		Language.addObject({
			'wcf.acp.quiz.quiz.image.error.invalidExtension': '{jslang}wcf.acp.quiz.quiz.image.error.invalidExtension{/jslang}'
		});
		new UZQuizAcpUploadQuestionImage({if $action == 'add'}0{else}{@$questionObj->questionID}{/if}, '{$tmpHash}');
	});
</script>

{include file='multipleLanguageInputJavascript' elementIdentifier='question' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='answerOne' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='answerTwo' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='answerThree' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='answerFour' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='answerFive' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='answerSix' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='comment' forceSelection=false}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.quiz.question.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='QuestionList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.quiz.question.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

{if $action == 'edit' && !$questionObj->isACP}
	<p class="warning">{lang}wcf.acp.quiz.question.user.warning{/lang}</p>
{/if}

<form id="formContainer" method="post" action="{if $action == 'add'}{link controller='QuestionAdd'}{/link}{else}{link controller='QuestionEdit' id=$questionObj->questionID}{/link}{/if}">
	<div class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.quiz.question.general{/lang}</h2>
		
		<!-- question -->
		<dl{if $errorField == 'question'} class="formError"{/if}>
			<dt><label for="question">{lang}wcf.acp.quiz.question.question{/lang}</label></dt>
			<dd>
				<textarea id="question" name="question" cols="40" rows="2">{$i18nPlainValues[question]}</textarea>
				{if $errorField == 'question'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<!-- categoryID -->
		{if QUIZ_CATEGORY_ON}
			<dl{if $errorField == 'categoryID'} class="formError"{/if}>
				<dt><label for="categoryID">{lang}wcf.acp.quiz.question.categoryID{/lang}</label></dt>
				<dd>
					<select id="categoryID" name="categoryID">
						<option value="0"{if $categoryID === null} selected="selected"{/if}>{lang}wcf.acp.quiz.question.categoryID.default{/lang}</option>
						{include file='categoryOptionList'}
					</select>
					
					{if $errorField == 'categoryID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.quiz.question.categoryID.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		<!-- correct -->
		<dl{if $errorField == 'correct'} class="formError"{/if}>
			<dt><label for="correct">{lang}wcf.acp.quiz.question.correct{/lang}</label></dt>
			<dd>
				<select name="correct" id="correct">
					<option value="0"{if $correct == 0} selected="selected"{/if}>0</option>
					<option value="1"{if $correct == 1} selected="selected"{/if}>1</option>
					<option value="2"{if $correct == 2} selected="selected"{/if}>2</option>
					<option value="3"{if $correct == 3} selected="selected"{/if}>3</option>
					<option value="4"{if $correct == 4} selected="selected"{/if}>4</option>
					<option value="5"{if $correct == 5} selected="selected"{/if}>5</option>
					<option value="6"{if $correct == 6} selected="selected"{/if}>6</option>
				</select>
				{if $errorField == 'correct'}
					<small class="innerError">
						{if $errorType}
							{lang}wcf.acp.quiz.question.correct.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</div>
	
	<div class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.quiz.question.answers{/lang}</h2>
		
		<!-- answerOne -->
		<dl{if $errorField == 'answerOne'} class="formError"{/if}>
			<dt><label for="answerOne">{lang}wcf.acp.quiz.question.answerOne{/lang}</label></dt>
			<dd>
				<textarea id="answerOne" name="answerOne" cols="40" rows="2">{$i18nPlainValues[answerOne]}</textarea>
				{if $errorField == 'answerOne'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<!-- answerTwo -->
		<dl{if $errorField == 'answerTwo'} class="formError"{/if}>
			<dt><label for="answerTwo">{lang}wcf.acp.quiz.question.answerTwo{/lang}</label></dt>
			<dd>
				<textarea id="answerTwo" name="answerTwo" cols="40" rows="2">{$i18nPlainValues[answerTwo]}</textarea>
				{if $errorField == 'answerTwo'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<!-- answerThree -->
		<dl{if $errorField == 'answerThree'} class="formError"{/if}>
			<dt><label for="answerThree">{lang}wcf.acp.quiz.question.answerThree{/lang}</label></dt>
			<dd>
				<textarea id="answerThree" name="answerThree" cols="40" rows="2">{$i18nPlainValues[answerThree]}</textarea>
				{if $errorField == 'answerThree'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{else}
							{lang}wcf.acp.quiz.question.answers.error.missing{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<!-- answerFour -->
		<dl{if $errorField == 'answerFour'} class="formError"{/if}>
			<dt><label for="answerFour">{lang}wcf.acp.quiz.question.answerFour{/lang}</label></dt>
			<dd>
				<textarea id="answerFour" name="answerFour" cols="40" rows="2">{$i18nPlainValues[answerFour]}</textarea>
				{if $errorField == 'answerFour'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{else}
							{lang}wcf.acp.quiz.question.answers.error.missing{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<!-- answerFive -->
		<dl{if $errorField == 'answerFive'} class="formError"{/if}>
			<dt><label for="answerFive">{lang}wcf.acp.quiz.question.answerFive{/lang}</label></dt>
			<dd>
				<textarea id="answerFive" name="answerFive" cols="40" rows="2">{$i18nPlainValues[answerFive]}</textarea>
				{if $errorField == 'answerFive'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{else}
							{lang}wcf.acp.quiz.question.answers.error.missing{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<!-- answerSix -->
		<dl{if $errorField == 'answerSix'} class="formError"{/if}>
			<dt><label for="answerSix">{lang}wcf.acp.quiz.question.answerSix{/lang}</label></dt>
			<dd>
				<textarea id="answerSix" name="answerSix" cols="40" rows="2">{$i18nPlainValues[answerSix]}</textarea>
				{if $errorField == 'answerSix'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<!-- comment -->
		<dl{if $errorField == 'comment'} class="formError"{/if}>
			<dt><label for="comment">{lang}wcf.acp.quiz.question.comment{/lang}</label></dt>
			<dd>
				<textarea id="comment" name="comment" cols="40" rows="2">{$i18nPlainValues[comment]}</textarea>
				<small>{lang}wcf.acp.quiz.question.comment.description{/lang}</small>
				{if $errorField == 'comment'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</div>
	
	<div class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.quiz.question.image{/lang}</h2>
		
		<!-- image -->
		<dl{if $errorField == 'image'} class="formError"{/if}>
			<dt><label for="image">{lang}wcf.acp.quiz.question.image{/lang}</label></dt>
			<dd class="framed">
				{if $action == 'add' || !$questionObj->getPreviewImage()}
					<img src="{@$__wcf->getPath()}images/quiz/question/default.png" alt="" id="questionImage">
				{else}
					<img src="{@$questionObj->getPreviewImage()}" width="90%" alt="" id="questionImage">
				{/if}
				
				<div id="uploadImage" style="margin-top:5px;"></div>
				{if $errorField == 'image'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.quiz.question.image.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.quiz.question.image.description{/lang}</small>
			</dd>
		</dl>
		
		<!-- deleteImage -->
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="deleteImage" name="deleteImage" value="1"> {lang}wcf.acp.quiz.question.deleteImage{/lang}</label>
			</dd>
		</dl>
	</div>
	
	<div class="formSubmit">
		<input id="saveButton" type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="tmpHash" value="{$tmpHash}">
		{csrfToken}
	</div>
</form>

{include file='footer'}
