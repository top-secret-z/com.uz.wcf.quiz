<section class="section">
    <h2 class="sectionTitle">{$data.title}</h2>

    {@$data.counter}
    {@$data.userCount}
    {@$data.averageRate}
    {@$data.maxRate}
    {@$data.minRate}
</section>

{if $data.tops|count}
    <section class="section">
        <h2 class="sectionTitle">{$data.topUserTitle}</h2>

        {foreach from=$data.tops key='username' item='count'}
            <dl><dt>{$username}</dt><dd>{@$count}</dd></dl>
        {/foreach}
    </section>
{/if}
