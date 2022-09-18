<section class="section">
    <h2 class="sectionTitle">{lang}wcf.user.quiz.rating{/lang}</h2>

    {if $rating}
        <p>{lang}wcf.user.quiz.alreadyRated{/lang}</p>
        <br>
    {/if}

    {if $hasSolved}
        <label><input type="radio" name="rating" value="5"{if $rating == 5} checked="checked"{/if}> {lang}{QUIZ_RATING_FIVE}{/lang}</label>
        <br>
        <label><input type="radio" name="rating" value="4"{if $rating == 4} checked="checked"{/if}> {lang}{QUIZ_RATING_FOUR}{/lang}</label>
        <br>
        <label><input type="radio" name="rating" value="3"{if $rating == 3} checked="checked"{/if}> {lang}{QUIZ_RATING_THREE}{/lang}</label>
        <br>
        <label><input type="radio" name="rating" value="2"{if $rating == 2} checked="checked"{/if}> {lang}{QUIZ_RATING_TWO}{/lang}</label>
        <br>
        <label><input type="radio" name="rating" value="1"{if $rating == 1} checked="checked"{/if}> {lang}{QUIZ_RATING_ONE}{/lang}</label>

    {else}
        <p>{lang}wcf.user.quiz.rating.mustPlay{/lang}</p>
    {/if}
</section>

<div class="formSubmit">
    {if $hasSolved}
        <button class="jsSubmitRating buttonPrimary" accesskey="s">{lang}wcf.user.quiz.button.rate{/lang}</button>
        {if $rating}
            <button class="jsDeleteRating">{lang}wcf.user.quiz.button.unrate{/lang}</button>
        {/if}
    {/if}
    <button class="jsAbortRating">{lang}wcf.user.quiz.button.abortrate{/lang}</button>
</div>
