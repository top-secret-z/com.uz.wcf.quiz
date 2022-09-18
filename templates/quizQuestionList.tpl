{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{$__wcf->getActivePage()->getTitle()} <span class="badge">{#$items}</span>{/capture}

{capture assign='headContent'}
    {if $pageNo < $pages}
        <link rel="next" href="{link controller='QuizQuestionList'}pageNo={@$pageNo+1}{/link}">
    {/if}
    {if $pageNo > 1}
        <link rel="prev" href="{link controller='QuizQuestionList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
    {/if}
{/capture}

{assign var='linkParameters' value=''}
{if $user}{append var='linkParameters' value='&userID='|concat:$user->userID}{/if}

{if WCF_VERSION|substr:0:3 >= '5.5'}
    {capture assign='contentHeaderNavigation'}
        <li><a href="{link controller='QuizQuestionAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.question.add{/lang}</span></a></li>
    {/capture}

    {capture assign='contentInteractionPagination'}
        {pages print=true assign=pagesLinks controller='QuizQuestionList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
    {/capture}

    {capture assign='contentInteractionButtons'}
        <div class="contentInteractionButton dropdown jsOnly">
            <a href="#" class="button small dropdownToggle"><span class="icon icon16 fa-sort-amount-{$sortOrder|strtolower}"></span> <span>{lang}wcf.user.quiz.button.sort{/lang}</span></a>
            <ul class="dropdownMenu">
                <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.time{/lang}{if $sortField == 'time'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=question&sortOrder={if $sortField == 'question' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.question{/lang}{if $sortField == 'question'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=count&sortOrder={if $sortField == 'count' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.count{/lang}{if $sortField == 'count'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                {if QUIZ_CATEGORY_ON}
                    <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.categoryID{/lang}{if $sortField == 'categoryID'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                {/if}
            </ul>
        </div>
    {/capture}

    {include file='header'}
{else}
    {capture assign='contentHeaderNavigation'}
        {if $items}
            {if $items > 1}
                <li class="dropdown jsOnly">
                    <a href="#" class="button dropdownToggle"><span class="icon icon16 fa-sort-amount-asc"></span> <span>{lang}wcf.user.quiz.button.sort{/lang}</span></a>
                    <ul class="dropdownMenu">
                        <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.time{/lang}{if $sortField == 'time'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=question&sortOrder={if $sortField == 'question' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.question{/lang}{if $sortField == 'question'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=count&sortOrder={if $sortField == 'count' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.count{/lang}{if $sortField == 'count'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        {if QUIZ_CATEGORY_ON}
                            <li><a href="{link controller='QuizQuestionList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.categoryID{/lang}{if $sortField == 'categoryID'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        {/if}
                    </ul>
                </li>
            {/if}
        {/if}

        <li><a href="{link controller='QuizQuestionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.question.add{/lang}</span></a></li>
    {/capture}

    {include file='header'}

    {hascontent}
        <div class="paginationTop">
            {content}
                {pages print=true assign=pagesLinks controller='QuizQuestionList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
            {/content}
        </div>
    {/hascontent}
{/if}

{if $items}
    <div class="section sectionContainerList">
        <ol class="containerList">
            {foreach from=$objects item=questObj}
                <li id="question{@$questObj->questionID}" class="jsQuestionRow">
                    <div class="containerHeadline">
                        <h3>
                            {if $questObj->approved}
                                <span class="icon icon24 fa-lock green jsTooltip pointer" title="{lang}wcf.user.quiz.question.inUse{/lang}"></span> 
                            {else}
                                {if $__wcf->getUser()->userID == $questObj->userID}
                                    <a href="{link controller='QuizQuestionEdit' object=$questObj}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon24 fa-pencil"></span></a>
                                    <span class="icon icon24 fa-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$questObj->questionID}" data-confirm-message="{lang}wcf.user.quiz.question.delete.sure{/lang}"></span>
                                {/if}
                            {/if}
                            {lang}{$questObj->question}{/lang}
                            {if $questObj->image && $__wcf->session->getPermission('user.quiz.canSubmitImage')}
                                <br><img src="{@$questObj->getPreviewImage()}" width="25%" alt="">
                            {/if}
                        </h3>
                        <p><span class="icon icon16 fa-clock-o"></span> {@$questObj->time|time}{if QUIZ_CATEGORY_ON}, {if $questObj->categoryID}{lang}{$categories[$questObj->categoryID]->getTitle()}{/lang}{else}{lang}wcf.acp.quiz.question.categoryID.default{/lang}{/if}{/if}{if $questObj->editedBy}, {lang}wcf.user.quiz.question.editedBy{/lang} {$questObj->editedBy}{/if}</p>

                        <ol class="questionList">
                            <li>{if $questObj->correct==1}<span class="icon icon16 green fa-check"></span>{/if} {lang}{$questObj->answerOne}{/lang}</li>
                            <li>{if $questObj->correct==2}<span class="icon icon16 green fa-check"></span>{/if} {lang}{$questObj->answerTwo}{/lang}</li>
                            {if $questObj->count >= 3}<li>{if $questObj->correct==3}<span class="icon icon16 green fa-check"></span>{/if} {lang}{$questObj->answerThree}{/lang}</li>{/if}
                            {if $questObj->count >= 4}<li>{if $questObj->correct==4}<span class="icon icon16 green fa-check"></span>{/if} {lang}{$questObj->answerFour}{/lang}</li>{/if}
                            {if $questObj->count >= 5}<li>{if $questObj->correct==5}<span class="icon icon16 green fa-check"></span>{/if} {lang}{$questObj->answerFive}{/lang}</li>{/if}
                            {if $questObj->count >= 6}<li>{if $questObj->correct==6}<span class="icon icon16 green fa-check"></span>{/if} {lang}{$questObj->answerSix}{/lang}</li>{/if}
                        </ol>
                    </div>
                </li>
            {/foreach}
        </ol>
    </div>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
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
                    <li><a href="{link controller='QuizQuestionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.quiz.question.add{/lang}</span></a></li>

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

<script data-relocate="true">
    $(function() {
        new WCF.Action.Delete('wcf\\data\\quiz\\question\\QuestionAction', '.jsQuestionRow');
    });
</script>

{include file='footer'}
