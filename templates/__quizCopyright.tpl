{if !"QUIZ_BRANDING_FREE"|defined}
	{if $templateName|isset && ($templateName=='quiz' || $templateName=='quizQuestionList' || $templateName=='quizResultList')}
	<div class="copyright">
			{lang}wcf.user.quiz.copyright{/lang}
		</div>
	{/if}
{/if}