{assign var='pageTitle' value='wcf.acp.quiz.stats'}

{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{@$pageTitle}{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
		
		<br>
		<ul class="inlineList dotSeparated">
			<li>{$stats['questions']}</li>
			<li>{$stats['users']}</li>
			<li>{$stats['average']}</li>
		</ul>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					<li><a href="{link controller='QuizList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.quiz.list{/lang}</span></a></li>
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller="QuizStats" id=$quiz->quizID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnResultID{if $sortField == 'resultID'} active {@$sortOrder}{/if}"><a href="{link controller='QuizStats' id=$quiz->quizID}pageNo={@$pageNo}&sortField=resultID&sortOrder={if $sortField == 'resultID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='QuizStats' id=$quiz->quizID}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.stats.time{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='QuizStats' id=$quiz->quizID}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.stats.username{/lang}</a></th>
					<th class="columnDigit columnResult{if $sortField == 'result'} active {@$sortOrder}{/if}"><a href="{link controller='QuizStats' id=$quiz->quizID}pageNo={@$pageNo}&sortField=result&sortOrder={if $sortField == 'result' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.stats.result{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=result}
					<tr>
						<td class="columnID columnResultID">{@$result->resultID}</td>
						<td class="columnText columnTime">{@$result->time|time}</td>
						<td class="columnIcon">{@$result->user->getAvatar()->getImageTag(24)}</td>
						<td class="columnText columnUsername">{$result->username}</td>
						<td class="columnDigit columnResult">{@$result->result|quizRound}</td>
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
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}
						<li><a href="{link controller='QuizList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.quiz.list{/lang}</span></a></li>
						
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.acp.quiz.stats.none{/lang}</p>
{/if}

{include file='footer'}
