{foreach from=$questions item=question}
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{$question.question}</h2>
			<p>{$question.language}</p>
		</header>
		
		<ol class="questionList">
			<li>{if $question.correct==1}<span class="icon icon16 green fa-check"></span>{/if} {$question.answerOne}</li>
			<li>{if $question.correct==2}<span class="icon icon16 green fa-check"></span>{/if} {$question.answerTwo}</li>
			{if $question.count >= 3}<li>{if $question.correct==3}<span class="icon icon16 green fa-check"></span>{/if} {$question.answerThree}</li>{/if}
			{if $question.count >= 4}<li>{if $question.correct==4}<span class="icon icon16 green fa-check"></span>{/if} {$question.answerFour}</li>{/if}
			{if $question.count >= 5}<li>{if $question.correct==5}<span class="icon icon16 green fa-check"></span>{/if} {$question.answerFive}</li>{/if}
			{if $question.count >= 6}<li>{if $question.correct==6}<span class="icon icon16 green fa-check"></span>{/if} {$question.answerSix}</li>{/if}
		</ol>
	</section>
{/foreach}
