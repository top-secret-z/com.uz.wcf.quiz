<ul class="sidebarItemList">
	{foreach from=$quizzes item=quiz}
		<li class="sidebarItemTitle">
			<h3><a href="{$quiz->getLink()}">{lang}{$quiz->title}{/lang}</a></h3>
			<small>{lang}wcf.user.quiz.box.newest{/lang}</small>
		</li>
	{/foreach}
</ul>