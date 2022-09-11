{include file='header' pageTitle='wcf.acp.quiz.quiz.'|concat:$action}

{if $questionList|count}
	<script data-relocate="true">
		require(['Language', 'UZ/Quiz/Acp/UploadQuizImage'], function(Language, UZQuizAcpUploadQuizImage) {
			Language.addObject({
				'wcf.acp.quiz.quiz.image.error.invalidExtension': '{jslang}wcf.acp.quiz.quiz.image.error.invalidExtension{/jslang}'
			});
			new UZQuizAcpUploadQuizImage({if $action == 'add'}0{else}{@$quiz->quizID}{/if}, '{$tmpHash}');
		});
		
		require(['Language', 'UZ/Quiz/Acp/ListQuestions'], function (Language, UZQuizAcpListQuestions) {
			Language.addObject({
				'wcf.acp.quiz.question.list': '{jslang}wcf.acp.quiz.question.list{/jslang}'
			});
			new UZQuizAcpListQuestions();
		});
		
		{if $action == 'add'}
			require(['UZ/Quiz/Acp/InsertQuestions'], function (UZQuizAcpInsertQuestions) {
				new UZQuizAcpInsertQuestions();
			});
		{/if}
	</script>
{/if}

{include file='multipleLanguageInputJavascript' elementIdentifier='text' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.quiz.quiz.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='QuizList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.quiz.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

{if $questionList|count}
	<form method="post" action="{if $action == 'add'}{link controller='QuizAdd'}{/link}{else}{link controller='QuizEdit' id=$quiz->quizID}{/link}{/if}">
		<div class="section tabMenuContainer">
			<nav class="tabMenu">
				<ul>
					<li><a href="{@$__wcf->getAnchor('tabGeneral')}">{lang}wcf.acp.quiz.quiz.general{/lang}</a></li>
					<li><a href="{@$__wcf->getAnchor('tabQuestions')}">{lang}wcf.acp.quiz.quiz.questions{/lang}</a></li>
					<li><a href="{@$__wcf->getAnchor('tabLimits')}">{lang}wcf.acp.quiz.quiz.limits{/lang}</a></li>
					{if QUIZ_GROUP_ON}
						<li><a href="{@$__wcf->getAnchor('tabGroups')}">{lang}wcf.acp.quiz.quiz.groups{/lang}</a></li>
					{/if}
				</ul>
			</nav>
			
			<div id="tabGeneral" class="tabMenuContent hidden">
				<div class="section">
					<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.general{/lang}</h2>
					
					<!-- title -->
					<dl{if $errorField == 'title'} class="formError"{/if}>
						<dt><label for="title">{lang}wcf.acp.quiz.quiz.title{/lang}</label></dt>
						<dd>
							<textarea id="title" name="title" cols="40" rows="1">{$i18nPlainValues[title]}</textarea>
							{if $errorField == 'title'}
								<small class="innerError">
									{if $errorType == 'empty' || $errorType == 'multilingual'}
										{lang}wcf.global.form.error.{$errorType}{/lang}
									{else}
										{lang}wcf.acp.quiz.quiz.title.error.{$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<!-- text -->
					<dl{if $errorField == 'text'} class="formError"{/if}>
						<dt><label for="text">{lang}wcf.acp.quiz.quiz.text{/lang}</label></dt>
						<dd>
							<textarea id="text" name="text" cols="40" rows="4">{$i18nPlainValues[text]}</textarea>
							{if $errorField == 'text'}
								<small class="innerError">
									{if $errorType == 'empty' || $errorType == 'multilingual'}
										{lang}wcf.global.form.error.{$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<!-- isActive -->
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="isActive" name="isActive" value="1"{if $isActive} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.isActive{/lang}</label>
						</dd>
					</dl>
					
					<!-- showOrder -->
					<dl>
						<dt><label for="showOrder">{lang}wcf.acp.quiz.quiz.showOrder{/lang}</label></dt>
						<dd>
							<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" min="0" class="short">
							<small>{lang}wcf.acp.quiz.quiz.showOrder.description{/lang}</small>
						</dd>
					</dl>
				</div>
				
				<div class="section">
					<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.image{/lang}</h2>
					
					<!-- image -->
					<dl{if $errorField == 'image'} class="formError"{/if}>
						<dt><label for="image">{lang}wcf.acp.quiz.quiz.image{/lang}</label></dt>
						<dd class="framed">
							<img src="{if $action == 'add'}{@$__wcf->getPath()}images/quiz/default.png{else}{@$quiz->getPreviewImage()}{/if}" alt="" id="quizImage">
							<div id="uploadImage" style="margin-top:5px;"></div>
							{if $errorField == 'image'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.quiz.quiz.image.error.{$errorType}{/lang}
									{/if}
								</small>
							{/if}
							<small>{lang}wcf.acp.quiz.quiz.image.description{/lang}</small>
						</dd>
					</dl>
					
					<!-- deleteImage -->
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="deleteImage" name="deleteImage" value="1"> {lang}wcf.acp.quiz.quiz.deleteImage{/lang}</label>
						</dd>
					</dl>
				</div>
				
				{event name='quizAdditional'}
			</div>
			
			<div id="tabQuestions" class="tabMenuContent hidden">
				<div class="section">
					<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.questions{/lang}</h2>
					
					<!-- questionIDs -->
					<dl{if $errorField == 'questionIDs'} class="formError"{/if}>
						<dt><label for="questionIDs">{lang}wcf.acp.quiz.quiz.questionIDs{/lang}</label></dt>
						<dd>
							<span class="button small jsListQuestionsButton" style="margin-bottom:5px;">{lang}wcf.acp.quiz.preview{/lang}</span>
							<textarea name="questionIDs" id="questionIDs" rows="10" cols="40">{$questionIDs}</textarea>
							<small>{lang}wcf.acp.quiz.quiz.questionIDs.description{/lang}</small>
							
							{if $errorField == 'questionIDs'}
								<small class="innerError">
								{if $errorType == 'missing'}
									{lang}wcf.acp.quiz.quiz.questionIDs.error.missing{/lang}
								{else}
									{lang}wcf.acp.quiz.quiz.questionIDs.error.{@$errorType}{/lang}
								{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<!-- autoInsert -->
					{if $action == 'add'}
						<dl>
							<dt><label>{lang}wcf.acp.quiz.quiz.autoInsert{/lang}</label></dt>
							<dd>
								<input type="number" id="insertField" value="10" min="1" class="tiny">
								{if QUIZ_CATEGORY_ON}
									<input type="checkbox" id="check0" name="categoryIDs" value="0">{lang}wcf.acp.quiz.question.categoryID.default{/lang}
									{foreach from=$categoryNodeList item=category}
										<input type="checkbox" id="check{$category->categoryID}" name="categoryIDs" value="{$category->categoryID}">
										{$category->getTitle()}
									{/foreach}
								{/if}
								<span class="button small jsInsertQuestionsButton">{lang}wcf.acp.quiz.quiz.insert{/lang}</span>
							</dd>
						</dl>
					{/if}
					
					<!-- randomize -->
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="randomize" name="randomize" value="1"{if $randomize} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.randomize{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.randomize.description{/lang}</small>
						</dd>
					</dl>
				</div>
				
				<div class="section">
					<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.answers{/lang}</h2>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showCorrect" name="showCorrect" value="1"{if $showCorrect} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.showCorrect{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.showCorrect.description{/lang}</small>
						</dd>
					</dl>
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showResult" name="showResult" value="1"{if $showResult} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.showResult{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.showResult.description{/lang}</small>
						</dd>
					</dl>
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showComment" name="showComment" value="1"{if $showComment} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.showComment{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.showComment.description{/lang}</small>
						</dd>
					</dl>
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showResultButton" name="showResultButton" value="1"{if $showResultButton} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.showResultButton{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.showResultButton.description{/lang}</small>
						</dd>
					</dl>
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showStats" name="showStats" value="1"{if $showStats} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.showStats{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.showStats.description{/lang}</small>
						</dd>
					</dl>
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showBest" name="showBest" value="1"{if $showBest} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.showBest{/lang}</label>
							<small>{lang}wcf.acp.quiz.quiz.showBest.description{/lang}</small>
						</dd>
					</dl>
				</div>
			</div>
			
			<div id="tabLimits" class="tabMenuContent hidden">
				<div class="section">
					<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.limits{/lang}</h2>
					
					<!-- hasPeriod -->
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="hasPeriod" name="hasPeriod" value="1"{if $hasPeriod} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.hasPeriod{/lang}</label>
						</dd>
					</dl>
					<script data-relocate="true">
						$('#hasPeriod').change(function (event) {
							if ($('#hasPeriod').is(':checked')) {
								$('#periodStartDl').show();
								$('#periodEndDl').show();
							}
							else {
								$('#periodStartDl').hide();
								$('#periodEndDl').hide();
							}
						});
						$('#hasPeriod').change();
					</script>
					
					<!-- periodStart -->
					<dl id="periodStartDl"{if $errorField == 'periodStart'} class="formError"{/if}>
						<dt><label for="periodStart">{lang}wcf.acp.quiz.quiz.period.start{/lang}</label></dt>
						<dd>
							<input type="datetime" id="periodStart" name="periodStart" value="{$periodStart}">
							
							{if $errorField == 'periodStart'}
								<small class="innerError">
									{lang}wcf.acp.quiz.quiz.period.start.error.{@$errorType}{/lang}
								</small>
							{/if}
						</dd>
					</dl>
					
					<!-- periodEnd -->
					<dl id="periodEndDl"{if $errorField == 'periodEnd'} class="formError"{/if}>
						<dt><label for="periodEnd">{lang}wcf.acp.quiz.quiz.period.end{/lang}</label></dt>
						<dd>
							<input type="datetime" id="periodEnd" name="periodEnd" value="{$periodEnd}">
							
							{if $errorField == 'periodEnd'}
								<small class="innerError">
									{lang}wcf.acp.quiz.quiz.period.end.error.{@$errorType}{/lang}
								</small>
							{/if}
						</dd>
					</dl>
					
					<!-- timeLimit -->
					<dl>
						<dt><label for="timeLimit">{lang}wcf.acp.quiz.quiz.timeLimit{/lang}</label></dt>
						<dd>
							<div class="inputAddon">
								<input type="number" name="timeLimit" value="{$timeLimit}" class="tiny" min="0" max="86399">
								<span class="inputSuffix">{lang}wcf.acp.option.suffix.seconds{/lang}</span>
							</div>
							<small>{lang}wcf.acp.quiz.quiz.timeLimit.description{/lang}</small>
						</dd>
					</dl>
					
					<!-- playAgain -->
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="playAgain" name="playAgain" value="1"{if $playAgain} checked="checked"{/if}> {lang}wcf.acp.quiz.quiz.playAgain{/lang}</label>
						</dd>
					</dl>
					
					<!-- paused -->
					<dl>
						<dt><label for="paused">{lang}wcf.acp.quiz.quiz.paused{/lang}</label></dt>
						<dd>
							<div class="inputAddon">
								<input type="number" name="paused" value="{$paused}" class="tiny" min="0" max="86399">
								<span class="inputSuffix">{lang}wcf.acp.option.suffix.minutes{/lang}</span>
							</div>
							<small>{lang}wcf.acp.quiz.quiz.paused.description{/lang}</small>
						</dd>
					</dl>
				</div>
				
				<div class="section">
					<header class="sectionHeader">
						<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.conditions{/lang}</h2>
						<p class="sectionDescription">{lang}wcf.acp.quiz.quiz.conditions.description{/lang}</p>
					</header>
					
					{if !$groupedObjectTypes|isset && $conditions|isset}{assign var='groupedObjectTypes' value=$conditions}{/if}
					
					{foreach from=$groupedObjectTypes key='conditionGroup' item='conditionObjectTypes'}
						<div id="user_{$conditionGroup}" class="tabMenuContent">
							{foreach from=$conditionObjectTypes item='condition'}
								{@$condition->getProcessor()->getHtml()}
							{/foreach}
						</div>
					{/foreach}
				</div>
			</div>
			
			{if QUIZ_GROUP_ON}
				<div id="tabGroups" class="tabMenuContent hidden">
					<div class="section">
						<header class="sectionHeader">
							<h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.groups{/lang}</h2>
							<p class="sectionDescription">{lang}wcf.acp.quiz.quiz.groups.description{/lang}</p>
						</header>
						
						<!-- assignResult -->
						<dl>
							<dt><label for="assignResult">{lang}wcf.acp.quiz.quiz.assignResult{/lang}</label></dt>
							<dd>
								<input type="number" name="assignResult" value="{$assignResult}" class="short" min="0" max="100">
								<small>{lang}wcf.acp.quiz.quiz.assignResult.description{/lang}</small>
							</dd>
						</dl>
						
						<!-- assignGroupIDs -->
						<dl{if $errorField == 'assignGroupIDs'} class="formError"{/if}>
							<dt><label>{lang}wcf.acp.quiz.quiz.assignGroupIDs{/lang}</label></dt>
							<dd>
								{htmlCheckboxes options=$availableGroups name=assignGroupIDs selected=$assignGroupIDs}
								{if $errorField == 'assignGroupIDs'}
									<small class="innerError">
										{if $errorType == 'invalidGroup'}{lang}wcf.acp.quiz.quiz.error.invalidGroup{/lang}{/if}
									</small>
								{/if}
							</dd>
						</dl>
					</div>
				</div>
			{/if}
		</div>
		
		<div class="formSubmit">
			<input id="saveButton" type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			<input type="hidden" name="tmpHash" value="{$tmpHash}">
			<input type="hidden" id="quiz_category_on" value="{QUIZ_CATEGORY_ON}">
			{csrfToken}
		</div>
	</form>
{else}
	<p class="error">{lang}wcf.acp.quiz.quiz.error.noQuestions{/lang}</p>
{/if}

{include file='footer'}
