<section class="section">
	<p>{lang}wcf.user.quiz.stats.created{/lang}, {lang}wcf.user.quiz.stats.questions{/lang}, {lang}wcf.user.quiz.stats.solves{/lang}.</p>
	<p>{lang}wcf.user.quiz.stats.rate{/lang}</p>
</section>

{if $userList|count}
	<section class="section sectionContainerList">
		{if $type == 'best'}
			<h2 class="sectionTitle">{lang}wcf.user.quiz.stats.best{/lang}</h2>
		{else}
			<h2 class="sectionTitle">{lang}wcf.user.quiz.stats.latest{/lang}</h2>
		{/if}
		
		<ol class="containerList jsGroupedUserList">
			{foreach from=$userList item=combi}
				{assign var='user' value=$combi.user}
				{assign var='rate' value=$combi.rate}
				<li>
					<div class="box48">
						<a href="{link controller='User' object=$user}{/link}" title="{$user->username}" class="framed">{@$user->getAvatar()->getImageTag(48)}</a>
						
						<div class="details userInformation">
							<div class="containerHeadline">
								<h3><a href="{link controller='User' object=$user}{/link}">{$user->username}</a>{if $user->banned} <span class="icon icon16 fa-lock jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}"></span>{/if}{if MODULE_USER_RANK && $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}</h3> 
							</div>
							<div>
								<p><strong>{$rate|quizRound} {lang}wcf.user.quiz.percent.text{/lang}</strong></p>
								<p><small>{@$combi.time|plainTime}</small></p>
							</div>
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</section>
{/if}
