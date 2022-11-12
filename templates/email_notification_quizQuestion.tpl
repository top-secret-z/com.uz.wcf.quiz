{if $mimeType === 'text/plain'}
{lang}{@$languageVariablePrefix}.mail.plaintext{/lang} 
{else}
    {lang}{@$languageVariablePrefix}.mail.html{/lang}
{/if}
