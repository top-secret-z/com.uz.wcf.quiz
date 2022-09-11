{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{$__wcf->getActivePage()->getTitle()} <span class="badge">{#$items}</span>{/capture}

{if $items}
	{capture assign='contentDescription'}{lang}wcf.user.quiz.stats.user{/lang}{/capture}
{/if}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='QuizResultList'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='QuizResultList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{if WCF_VERSION|substr:0:3 >= '5.5'}
	{capture assign='contentInteractionPagination'}
		{pages print=true assign=pagesLinks controller='QuizResultList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	{/capture}
	
	{include file='header'}
{else}
	{include file='header'}
	
	{hascontent}
		<div class="paginationTop">
			{content}
				{pages print=true assign=pagesLinks controller="QuizResultList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
			{/content}
		</div>
	{/hascontent}
{/if}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='QuizResultList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.quiz.time{/lang}</a></th>
					<th class="columnText columnResult{if $sortField == 'result'} active {@$sortOrder}{/if}"><a href="{link controller='QuizResultList'}pageNo={@$pageNo}&sortField=result&sortOrder={if $sortField == 'result' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.quiz.result{/lang}</a></th>
					<th class="columnText columnTitle{if $sortField == 'quizTitle'} active {@$sortOrder}{/if}"><a href="{link controller='QuizResultList'}pageNo={@$pageNo}&sortField=quizTitle&sortOrder={if $sortField == 'quizTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.quiz.quiz{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=result}
					<tr>
						<td class="columnText columnTime">{@$result->time|date}</td>
						<td class="columnText columnResult">{@$result->result|quizRound} {lang}wcf.user.quiz.percent{/lang}</td>
						<td class="columnText columnTitle">
							<a href="{link controller='Quiz' resultID=$result->quizID}#quiz{$result->quizID}{/link}">{lang}{$result->quizTitle|tableWordwrap}{/lang}</a>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.user.quiz.noSolves{/lang}</p>
{/if}

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
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
