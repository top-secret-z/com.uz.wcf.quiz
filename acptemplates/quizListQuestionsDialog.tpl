<section class="section sectionContainerList">
    <h2 class="sectionTitle">{lang}wcf.acp.quiz.quiz.questionsSelected{/lang}</h2>

    {if $questions|count}
        <ul class="containerList">
            {foreach from=$questions item=combi}
                {assign var='id' value=$combi.id}
                {assign var='question' value=$combi.question}

                <li>
                    <div class="box48">
                        <span style="width:48px;">{@$id}</span>
                        <div>{lang}{$question}{/lang}</div>
                    </div>
                </li>
            {/foreach}
        </ul>
    {/if}
</section>
