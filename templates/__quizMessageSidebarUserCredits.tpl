{if $__wcf->session->getPermission('user.quiz.canSee')}
    {if QUIZ_USER_QUIZZES && $userProfile->uzQuiz}
        <dt><a href="{link controller='Quiz'}{/link}" title="{lang}wcf.user.quiz.open{/lang}" class="jsTooltip">{lang}wcf.user.quiz.userSolves{/lang}</a></dt>
        <dd>{#$userProfile->uzQuiz}</dd>
    {/if}
    {if QUIZ_USER_QUIZRATES && $userProfile->uzQuiz}
        <dt><a href="{link controller='Quiz'}{/link}" title="{lang}wcf.user.quiz.open{/lang}" class="jsTooltip">{lang}wcf.user.quiz.userRates{/lang}</a></dt>
        <dd>{$userProfile->uzQuizRate|quizRound} {lang}wcf.user.quiz.percent{/lang}</dd>
    {/if}
{/if}
