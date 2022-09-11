<ul class="sidebarItemList">
	{foreach from=$users item=user}
		<li class="box24">
			<a href="{link controller='User' object=$user}{/link}" class="framed">{@$user->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarItemTitle">
				<h3><a href="{link controller='User' object=$user}{/link}" class="userLink" data-user-id="{@$user->userID}">{$user->username}</a></h3>
				<small>{lang}wcf.user.quiz.box.bestSolver{/lang}</small>
			</div>
		</li>
	{/foreach}
</ul>