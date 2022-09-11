{include file='header' pageTitle='wcf.acp.quiz.question.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\quiz\\question\\QuestionAction', '.jsQuestionRow');
		new WCF.Action.Toggle('wcf\\data\\quiz\\question\\QuestionAction', '.jsQuestionRow');
	});
	
	require(['Language', 'UZ/Quiz/Acp/ListAnswers'], function (Language, UZQuizAcpListAnswers) {
		Language.addObject({
			'wcf.acp.quiz.question.preview': '{jslang}wcf.acp.quiz.question.preview{/jslang}'
		});
		
		new UZQuizAcpListAnswers();
	});
	
	require(['Language', 'UZ/Quiz/Acp/ListQuizzes'], function (Language, UZQuizAcpListQuizzes) {
		Language.addObject({
			'wcf.acp.quiz.quizList': '{jslang}wcf.acp.quiz.quizList{/jslang}'
		});
		
		new UZQuizAcpListQuizzes();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.quiz.question.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='QuestionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.question.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $objects|count}
	<form method="post" action="{link controller='QuestionList'}{/link}">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<input type="text" id="filter" name="filter" value="{$filter}" autocomplete="off" placeholder="{lang}wcf.acp.quiz.filter.placeholder{/lang}" class="long">
						<small>{lang}wcf.acp.quiz.filter.description{/lang}</small>
					</dd>
				</dl>
				
				{event name='filterFields'}
			</div>
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				{csrfToken}
			</div>
		</section>
	</form>
{/if}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="QuestionList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnQuestionID{if $sortField == 'questionID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=questionID&sortOrder={if $sortField == 'questionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.acp.quiz.question.time{/lang}</a></th>
					{if QUIZ_CATEGORY_ON}
						<th class="columnText columnCategoryID{if $sortField == 'categoryID'} active {@$sortOrder}{/if}"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.acp.quiz.question.categoryID{/lang}</a></th>
					{/if}
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.acp.quiz.question.username{/lang}</a></th>
					<th class="columnText columnCount{if $sortField == 'count'} active {@$sortOrder}{/if}"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=count&sortOrder={if $sortField == 'count' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.acp.quiz.question.count{/lang}</a></th>
					<th class="columnText columnImage{if $sortField == 'image'} active {@$sortOrder}{/if}"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=image&sortOrder={if $sortField == 'image' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.acp.quiz.question.image{/lang}</a></th>
					<th class="columnText columnQuestion{if $sortField == 'question'} active {@$sortOrder}{/if}"><a href="{link controller='QuestionList'}pageNo={@$pageNo}&sortField=question&sortOrder={if $sortField == 'question' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&filter={$filter}{/link}">{lang}wcf.acp.quiz.question.question{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=question}
					<tr class="jsQuestionRow">
						<td class="columnIcon">
							{if !$question->isUsedByQuiz()}
								<span class="icon icon16 fa-{if $question->approved}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$question->approved}enable{else}disable{/if}{/lang}" data-object-id="{@$question->questionID}"></span>
							{else}
								<span class="icon icon16 disabled fa-check"></span>
							{/if}
							<a href="{link controller='QuestionEdit' object=$question}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if !$question->isUsedByQuiz()}
								<span class="icon icon16 fa-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$question->questionID}" data-confirm-message="{lang}wcf.acp.quiz.question.delete.sure{/lang}"></span>
							{else}
								<span class="icon icon16 fa-lock jsListQuizzesButton jsTooltip pointer" title="{lang}wcf.acp.quiz.question.used{/lang}" data-object-id="{@$question->questionID}"></span>
							{/if}
							<span class="icon icon16 fa-eye jsListAnswersButton jsTooltip pointer" title="{lang}wcf.acp.quiz.question.preview{/lang}" data-object-id="{@$question->questionID}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$question->questionID}</td>
						<td class="columnTime columnTime">{@$question->time|time}</td>
						{if QUIZ_CATEGORY_ON}
							{if $question->categoryID}
								<td class="columnText columnCategoryID">{lang}{$categories[$question->categoryID]->getTitle()}{/lang}</td>
							{else}
								<td class="columnText columnCategoryID">{lang}wcf.acp.quiz.question.categoryID.default{/lang}</td>
							{/if}
						{/if}
						<td class="columnText columnUsername">{$question->username}</td>
						<td class="columnText columnCount">{$question->count}</td>
						<td class="columnText columnImage">{$question->image}</td>
						<td class="columnText columnQuestion">{lang}{$question->question}{/lang}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='QuestionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.question.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
