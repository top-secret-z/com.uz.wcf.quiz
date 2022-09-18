<section class="section sectionContainerList">
    <h2 class="sectionTitle">{lang}wcf.acp.quiz.question.usedBy{/lang}</h2>

    {if $quizes|count}
        <ul class="containerList">
            {foreach from=$quizes item=quiz}
                <li>
                    <div class="box32">
                        <a href="{link controller='QuizEdit' object=$quiz}{/link}" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon24 fa-pencil"></span></a>
                        <div>
                            {lang}{$quiz->title}{/lang}
                        </div>
                    </div>
                </li>
            {/foreach}
        </ul>
    {else}
        <p class="marginTop">{lang}wcf.acp.quiz.noQuiz{/lang}</p>
    {/if}
</section>
