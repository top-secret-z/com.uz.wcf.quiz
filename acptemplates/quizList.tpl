{include file='header' pageTitle='wcf.acp.quiz.quiz.list'}

<script data-relocate="true">
    require(['Language', 'UZ/Quiz/Acp/ShowStats'], function (Language, UZQuizAcpShowStats) {
        Language.addObject({
            'wcf.acp.quiz.stats': '{jslang}wcf.acp.quiz.stats{/jslang}'
        });
        new UZQuizAcpShowStats();
    });

    $(function() {
        new WCF.Action.Delete('wcf\\data\\quiz\\QuizAction', '.jsQuizRow');
        new WCF.Action.Toggle('wcf\\data\\quiz\\QuizAction', $('.jsQuizRow'));
    });
</script>

<header class="contentHeader">
    <div class="contentHeaderTitle">
        <h1 class="contentTitle">{lang}wcf.acp.quiz.quiz.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
    </div>

    <nav class="contentHeaderNavigation">
        <ul>
            <li><a href="{link controller='QuizAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.quiz.add{/lang}</span></a></li>

            {event name='contentHeaderNavigation'}
        </ul>
    </nav>
</header>

{hascontent}
    <div class="paginationTop">
        {content}{pages print=true assign=pagesLinks controller="QuizList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
    </div>
{/hascontent}

{if $objects|count}
    <div class="section tabularBox">
        <table class="table">
            <thead>
                <tr>
                    <th class="columnID columnQuizID{if $sortField == 'quizID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=quizID&sortOrder={if $sortField == 'quizID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
                    <th class="columnText columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.quiz.showOrder{/lang}</a></th>
                    <th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.quiz.time{/lang}</a></th>
                    <th class="columnText columnQuestions{if $sortField == 'questions'} active {@$sortOrder}{/if}"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=questions&sortOrder={if $sortField == 'questions' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.quiz.questions{/lang}</a></th>
                    <th class="columnText columnPeriod{if $sortField == 'hasPeriod'} active {@$sortOrder}{/if}"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=hasPeriod&sortOrder={if $sortField == 'hasPeriod' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.quiz.expire{/lang}</a></th>
                    <th class="columnText columnTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.quiz.title{/lang}</a></th>
                    <th class="columnText columnCounter{if $sortField == 'counter'} active {@$sortOrder}{/if}"><a href="{link controller='QuizList'}pageNo={@$pageNo}&sortField=counter&sortOrder={if $sortField == 'counter' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.quiz.quiz.counter{/lang}</a></th>

                    {event name='columnHeads'}
                </tr>
            </thead>

            <tbody>
                {foreach from=$objects item=quiz}
                    <tr class="jsQuizRow">
                        <td class="columnIcon">
                            <span class="icon icon16 fa-{if $quiz->isActive}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$quiz->isActive}enable{else}disable{/if}{/lang}" data-object-id="{@$quiz->quizID}"></span>
                            <a href="{link controller='QuizEdit' object=$quiz}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
                            <span class="icon icon16 fa-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$quiz->quizID}" data-confirm-message="{lang}wcf.acp.quiz.quiz.delete.sure{/lang}"></span>
                            <a href="{link controller='QuizStats' object=$quiz}{/link}" title="{lang}wcf.acp.quiz.stats{/lang}" class="jsTooltip"><span class="icon icon16 fa-bar-chart"></span></a>

                            {event name='rowButtons'}
                        </td>
                        <td class="columnID">{@$quiz->quizID}</td>
                        <td class="columnText columnShowOrder">{$quiz->showOrder}</td>
                        <td class="columnText columnTime">{@$quiz->time|time}</td>
                        <td class="columnText columnQuestions">{@$quiz->questions}</td>
                        <td class="columnText columnPeriod">{lang}wcf.acp.quiz.{if $quiz->hasPeriod}yes{else}no{/if}{/lang}</td>
                        <td class="columnText columnTitle">{lang}{$quiz->title}{/lang}</td>
                        <td class="columnText columnCounter">{$quiz->counter}</td>

                        {event name='columns'}
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

        <nav class="contentFooterNavigation">
            <ul>
                <li><a href="{link controller='QuizAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.quiz.add{/lang}</span></a></li>

                {event name='contentFooterNavigation'}
            </ul>
        </nav>
    </footer>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
