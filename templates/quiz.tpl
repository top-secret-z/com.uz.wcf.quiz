{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{$__wcf->getActivePage()->getTitle()} <span class="badge">{#$items}</span>{/capture}

{capture assign='headContent'}
    {if $pageNo < $pages}
        <link rel="next" href="{link controller='Quiz'}pageNo={@$pageNo+1}{/link}">
    {/if}
    {if $pageNo > 1}
        <link rel="prev" href="{link controller='Quiz'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
    {/if}

    {if $__wcf->getUser()->userID}
        <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='QuizFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
    {else}
        <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='QuizFeed'}{/link}">
    {/if}
{/capture}

{assign var='linkParameters' value=''}

{if WCF_VERSION|substr:0:3 >= '5.5'}
    {capture assign='contentInteractionPagination'}
        {if QUIZ_FILTER}
            {pages print=true assign='pagesLinks' controller='Quiz' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&filter=$filter"}
        {else}
            {pages print=true assign='pagesLinks' controller='Quiz' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
        {/if}
    {/capture}

    {capture assign='contentInteractionButtons'}
        <div class="contentInteractionButton dropdown jsOnly">
            <a href="#" class="button small dropdownToggle"><span class="icon icon16 fa-sort-amount-{$sortOrder|strtolower}"></span> <span>{lang}wcf.user.quiz.button.sort{/lang}</span></a>
            <ul class="dropdownMenu">
                <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.showOrder{/lang}{if $sortField == 'showOrder'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.title{/lang}{if $sortField == 'title'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                {if QUIZ_RATING_ACTIVATE && $__wcf->getSession()->getPermission('user.quiz.canSeeRating')}
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=ratingTotal&sortOrder={if $sortField == 'ratingTotal' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.ratingTotal{/lang}{if $sortField == 'ratingTotal'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                {/if}
                <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.time{/lang}{if $sortField == 'time'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=counter&sortOrder={if $sortField == 'counter' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.counter{/lang}{if $sortField == 'counter'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=questions&sortOrder={if $sortField == 'questions' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.questions{/lang}{if $sortField == 'questions'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=timeLimit&sortOrder={if $sortField == 'timeLimit' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.timeLimit{/lang}{if $sortField == 'timeLimit'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
            </ul>
        </div>

        {if QUIZ_FILTER}
            <div class="contentInteractionButton dropdown jsOnly">
                <a href="#" class="button{if $filter>0} buttonPrimary{/if} small dropdownToggle"><span class="icon icon16 fa-filter"></span> <span>{lang}wcf.global.filter{/lang}</span></a>
                <ul class="dropdownMenu">
                    <li><a href="{link controller='Quiz'}pageNo=1&sortField={$sortField}&sortOrder={$sortOrder}&filter=0{@$linkParameters}{/link}">{lang}wcf.user.quiz.filter.all{/lang}{if $filter == 0} <span class="icon icon16 fa-caret-left"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo=1&sortField={$sortField}&sortOrder={$sortOrder}&filter=1{@$linkParameters}{/link}">{lang}wcf.user.quiz.filter.unplayed{/lang}{if $filter == 1} <span class="icon icon16 fa-caret-left"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo=1&sortField={$sortField}&sortOrder={$sortOrder}&filter=2{@$linkParameters}{/link}">{lang}wcf.user.quiz.filter.played{/lang}{if $filter == 2} <span class="icon icon16 fa-caret-left"></span>{/if}</a></li>
                </ul>
            </div>
        {/if}

        <a href="#" class="markAllAsReadButton contentInteractionButton button small jsOnly"><span class="icon icon16 fa-check"></span> <span>{lang}wcf.user.quiz.markAsRead{/lang}</span></a>
    {/capture}

    {capture assign='contentInteractionDropdownItems'}
        <li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link controller='QuizFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link controller='QuizFeed'}{/link}{/if}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
    {/capture}

    {include file='header'}
{else}
    {capture assign='headerNavigation'}
        <li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link controller='QuizFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link controller='QuizFeed'}{/link}{/if}" title="{lang}wcf.global.button.rss{/lang}" class="jsTooltip"><span class="icon icon16 fa-rss"></span> <span class="invisible">{lang}wcf.global.button.rss{/lang}</span></a></li>
        <li class="jsOnly"><a href="#" title="{lang}wcf.user.quiz.markAsRead{/lang}" class="markAllAsReadButton jsTooltip"><span class="icon icon16 fa-check"></span> <span class="invisible">{lang}wcf.user.quiz.markAsRead{/lang}</span></a></li>
    {/capture}

    {capture assign='contentHeaderNavigation'}
        {if $items > 1}
            <li class="dropdown jsOnly">
                <a href="#" class="button dropdownToggle"><span class="icon icon16 fa-sort-amount-asc"></span> <span>{lang}wcf.user.quiz.button.sort{/lang}</span></a>
                <ul class="dropdownMenu">
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.showOrder{/lang}{if $sortField == 'showOrder'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.title{/lang}{if $sortField == 'title'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    {if QUIZ_RATING_ACTIVATE && $__wcf->getSession()->getPermission('user.quiz.canSeeRating')}
                        <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=ratingTotal&sortOrder={if $sortField == 'ratingTotal' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.ratingTotal{/lang}{if $sortField == 'ratingTotal'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    {/if}
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.time{/lang}{if $sortField == 'time'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=counter&sortOrder={if $sortField == 'counter' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.counter{/lang}{if $sortField == 'counter'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=questions&sortOrder={if $sortField == 'questions' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.questions{/lang}{if $sortField == 'questions'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo={@$pageNo}&sortField=timeLimit&sortOrder={if $sortField == 'timeLimit' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{if QUIZ_FILTER}&filter={@$filter}{/if}{@$linkParameters}{/link}">{lang}wcf.user.quiz.sort.timeLimit{/lang}{if $sortField == 'timeLimit'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                </ul>
            </li>
        {/if}
        {if QUIZ_FILTER}
            <li class="dropdown jsOnly">
                <a href="#" class="button{if $filter>0} buttonPrimary{/if} dropdownToggle"><span class="icon icon16 fa-filter"></span> <span>{lang}wcf.global.filter{/lang}</span></a>
                <ul class="dropdownMenu">
                    <li><a href="{link controller='Quiz'}pageNo=1&sortField={$sortField}&sortOrder={$sortOrder}&filter=0{@$linkParameters}{/link}">{lang}wcf.user.quiz.filter.all{/lang}{if $filter == 0} <span class="icon icon16 fa-caret-left"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo=1&sortField={$sortField}&sortOrder={$sortOrder}&filter=1{@$linkParameters}{/link}">{lang}wcf.user.quiz.filter.unplayed{/lang}{if $filter == 1} <span class="icon icon16 fa-caret-left"></span>{/if}</a></li>
                    <li><a href="{link controller='Quiz'}pageNo=1&sortField={$sortField}&sortOrder={$sortOrder}&filter=2{@$linkParameters}{/link}">{lang}wcf.user.quiz.filter.played{/lang}{if $filter == 2} <span class="icon icon16 fa-caret-left"></span>{/if}</a></li>
                </ul>
            </li>
        {/if}
    {/capture}

    {include file='header'}

    {hascontent}
        <div class="paginationTop">
            {content}
                {if QUIZ_FILTER}
                    {pages print=true assign='pagesLinks' controller='Quiz' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&filter=$filter"}
                {else}
                    {pages print=true assign='pagesLinks' controller='Quiz' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
                {/if}
            {/content}
        </div>
    {/hascontent}
{/if}

{if $items}
    <div class="section">
        <div id="quizListContainer">
            {foreach from=$objects item=quiz}
                <section class="section" id="quiz{@$quiz->quizID}">
                    <h2 class="sectionTitle">{lang}{$quiz->title}{/lang}</h2>

                    {if QUIZ_RATING_ACTIVATE && $__wcf->getSession()->getPermission('user.quiz.canSeeRating')}
                        {assign var='brating' value=$quiz->ratingTotal*2|round}
                        <span class="quizRating quizRating-{$brating*5} jsTooltip {if $__wcf->getSession()->getPermission('user.quiz.canSeeRatingDetails')}ratingDetails pointer{/if}" data-object-id={@$quiz->quizID} title="{$quiz->ratingTotal|quizRound} ({$quiz->ratingCount} {if $quiz->ratingCount==1}{lang}wcf.user.quiz.user{/lang}{else}{lang}wcf.user.quiz.users{/lang}{/if})"></span>
                    {/if}

                    {assign var='solved' value=$quiz->hasSolved()}
                    {assign var='period' value=$quiz->getPeriod()}
                    {assign var='canPlay' value=$quiz->canPlay()}
                    {assign var='mustPause' value=$quiz->mustPause()}
                    {assign var='xhours' value=$mustPause/3600|floor}
                    {assign var='xmins' value=($mustPause-$xhours*3600)/60|floor}
                    {assign var='xsecs' value=$mustPause-$xhours*3600-$xmins*60}

                    <div class="quizBox framed{if !$quiz->isActive} quizDisabled{/if} clearfix">
                        <img src="{@$quiz->getPreviewImage()}" class="quizThumbnail" alt="">
                        <div>
                            <p>{if !$quiz->isActive}<span class="icon icon24 fa-wrench red jsTooltip" title="{lang}wcf.user.quiz.isDisabled{/lang}"></span>{/if} {event name='additionalIcon'} {if $quiz->timeLimit}<span class="icon icon24 fa-clock-o red jsTooltip" title="{lang}wcf.user.quiz.timedQuiz{/lang}"></span>{/if} {if $period}<span class="icon icon24 fa-{if $period==2}unlock{else}lock{/if} jsTooltip" title="{lang}wcf.user.quiz.period{if $period==1}Pending{elseif $period==2}Valid{else}Expired{/if}{/lang}"></span>{/if} <strong>{lang}wcf.user.quiz.stats.created{/lang} | {lang}wcf.user.quiz.stats.questions{/lang} | {lang}wcf.user.quiz.stats.solves{/lang} </strong> {if $solved} <span class="icon icon24 fa-check green jsTooltip" title="{lang}wcf.user.quiz.played{/lang}"></span>{/if}</p>
                            <p>{if $canPlay && $mustPause}<br><strong>{lang}wcf.user.quiz.mustPause{/lang}</strong><br><br>{/if}{lang}{$quiz->text}{/lang}</p>
                        </div>
                    </div>

                    {if $canPlay && !$mustPause}
                        <button class="quizButton jsOnly" data-object-id={@$quiz->quizID}>{lang}wcf.user.quiz.start{/lang}</button>
                    {/if}
                    {if $quiz->counter && ($canPlay || $solved)}
                        {if QUIZ_RATING_ACTIVATE && $__wcf->getSession()->getPermission('user.quiz.canRate')}
                            <button class="rateButton jsOnly" data-object-id={@$quiz->quizID}>{lang}wcf.user.quiz.rate{/lang}</button>
                        {/if}
                        {if $quiz->showStats}<button class="statsButton jsOnly" data-object-id={@$quiz->quizID}>{lang}wcf.user.quiz.stats{/lang}</button>{/if}
                        {if $quiz->showBest}<button class="bestButton jsOnly" data-object-id={@$quiz->quizID}>{lang}wcf.user.quiz.best{/lang}</button>{/if}
                    {/if}
                </section>
            {/foreach}
        </div>

        <div id="quizContainer">
            <div id="beforeQuiz"></div>

            <section class="section">
                <h2 class="sectionTitle" id="quizTitle"></h2>

                <div id="quizTimer" class="quizQuestionTimer">{lang}wcf.user.quiz.timeLeft{/lang}:  <span id="quizTimerValue"></span></div>

                <div id="quiz"></div>
            </section>
        </div>

        <div id="quizSubmitContainer" class="quizSubmit">
            <button id="quizSaveButton" class="jsOnly">{lang}wcf.user.quiz.button.save{/lang}</button>
            <button id="quizNextButton" class="jsOnly">{lang}wcf.user.quiz.button.next{/lang}</button>
            <button id="quizAbortButton" class="jsOnly">{lang}wcf.user.quiz.button.abort{/lang}</button>
            <button id="quizResultButton" class="jsOnly">{lang}wcf.user.quiz.button.result{/lang}</button>
            <button id="quizStatsButton" class="jsOnly">{lang}wcf.user.quiz.button.stats{/lang}</button>
            <button id="quizBestButton" class="jsOnly">{lang}wcf.user.quiz.button.best{/lang}</button>
            <button id="quizOverviewButton" class="jsOnly">{lang}wcf.user.quiz.button.overview{/lang}</button>
            {csrfToken}
        </div>

        <div id="afterQuiz"></div>
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

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

<script data-relocate="true" src="{@$__wcf->getPath()}/js/QUIZ{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
    $(function() {
        WCF.Language.addObject({
            'wcf.user.quiz.abortConfirm':                    '{jslang}wcf.user.quiz.abortConfirm{/jslang}',
            'wcf.user.quiz.browserAbort':                    '{jslang}wcf.user.quiz.browserAbort{/jslang}',
            'wcf.user.quiz.correct':                        '{jslang}wcf.user.quiz.correct{/jslang}',
            'wcf.user.quiz.error.noSelection':                '{jslang}wcf.user.quiz.error.noSelection{/jslang}',
            'wcf.user.quiz.question':                        '{jslang}wcf.user.quiz.question{/jslang}',
            'wcf.user.quiz.questions':                        '{jslang}wcf.user.quiz.questions{/jslang}',
            'wcf.user.quiz.result.correct':                    '{jslang}wcf.user.quiz.result.correct{/jslang}',
            'wcf.user.quiz.result.saved':                    '{jslang}wcf.user.quiz.result.saved{/jslang}',
            'wcf.user.quiz.result.wrong':                    '{jslang}wcf.user.quiz.result.wrong{/jslang}',
            'wcf.user.quiz.result.your':                    '{jslang}wcf.user.quiz.result.your{/jslang}',
            'wcf.user.quiz.resultDialog.correct':            '{jslang}wcf.user.quiz.resultDialog.correct{/jslang}',
            'wcf.user.quiz.resultDialog.correct.answers':    '{jslang}wcf.user.quiz.resultDialog.correct.answers{/jslang}',
            'wcf.user.quiz.resultDialog.hide':                '{jslang}wcf.user.quiz.resultDialog.hide{/jslang}',
            'wcf.user.quiz.resultDialog.none':                '{jslang}wcf.user.quiz.resultDialog.none{/jslang}',
            'wcf.user.quiz.resultDialog.result':            '{jslang}wcf.user.quiz.resultDialog.result{/jslang}',
            'wcf.user.quiz.resultDialog.wrong':                '{jslang}wcf.user.quiz.resultDialog.wrong{/jslang}',
            'wcf.user.quiz.best':                            '{jslang}wcf.user.quiz.best{/jslang}',
            'wcf.user.quiz.stats':                            '{jslang}wcf.user.quiz.stats{/jslang}',
            'wcf.user.quiz.timeExpired':                    '{jslang}wcf.user.quiz.timeExpired{/jslang}',
            'wcf.user.quiz.timeExpired.title':                '{jslang}wcf.user.quiz.timeExpired.title{/jslang}',
            'wcf.user.quiz.rating':                            '{jslang}wcf.user.quiz.rating{/jslang}',
            'wcf.user.quiz.rating.rate.success':            '{jslang}wcf.user.quiz.rating.rate.success{/jslang}',
            'wcf.user.quiz.rating.unrate.success':            '{jslang}wcf.user.quiz.rating.unrate.success{/jslang}',
            'wcf.user.quiz.rating.details':                    '{jslang}wcf.user.quiz.rating.details{/jslang}'
        });

        new QUIZ.Quiz();
        new QUIZ.MarkAllAsRead();
        new QUIZ.Rating();
    });
</script>

{include file='footer'}
